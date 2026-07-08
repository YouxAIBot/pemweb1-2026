<?php

use App\Models\Ad;
use App\Models\DuelPlayer;
use App\Models\DuelQuestion;
use App\Models\DuelSession;
use App\Models\LearningLanguage;
use App\Models\LearningLevel;
use App\Models\LearningPart;
use App\Models\LearningQuestion;
use App\Models\LearningQuestionOption;
use App\Models\PremiumPackage;
use App\Models\PremiumPayment;
use App\Models\QuizRoom;
use App\Models\QuizRoomHistory;
use App\Models\QuizRoomMember;
use App\Models\QuizRoomOption;
use App\Models\QuizRoomQuestion;
use App\Models\User;
use App\Models\UserLearningProfile;
use App\Models\UserLevelProgress;
use App\Models\UserPremium;
use App\Models\UserQuestionProgress;
use App\Services\PremiumActivationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function yoLearningCourseFixture(User $user, array $levelOverrides = []): array
{
    $suffix = uniqid();
    $language = LearningLanguage::create([
        'name' => 'English ' . $suffix,
        'slug' => 'english-' . $suffix,
        'native_name' => 'English',
        'flag_label' => 'EN',
        'is_active' => true,
    ]);

    $part = LearningPart::create([
        'learning_language_id' => $language->id,
        'title' => 'Part 1',
        'slug' => 'part-' . $suffix,
        'level_number' => 1,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $level = LearningLevel::create(array_merge([
        'learning_part_id' => $part->id,
        'title' => 'Level 1',
        'slug' => 'level-1-' . $suffix,
        'type' => 'multiple_choice',
        'short_label' => '1',
        'sort_order' => 1,
        'xp_reward' => 10,
        'passing_score' => 70,
        'is_active' => true,
    ], $levelOverrides));

    $nextLevel = LearningLevel::create([
        'learning_part_id' => $part->id,
        'title' => 'Level 2',
        'slug' => 'level-2-' . $suffix,
        'type' => 'multiple_choice',
        'short_label' => '2',
        'sort_order' => 2,
        'xp_reward' => 10,
        'passing_score' => 70,
        'is_active' => true,
    ]);

    $question = LearningQuestion::create([
        'learning_level_id' => $level->id,
        'type' => 'multiple_choice',
        'instruction' => 'Pilih jawaban benar.',
        'question_text' => 'Hello berarti?',
        'points' => 10,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    LearningQuestionOption::create([
        'learning_question_id' => $question->id,
        'option_text' => 'Halo',
        'is_correct' => true,
        'sort_order' => 1,
    ]);

    UserLearningProfile::create([
        'user_id' => $user->id,
        'learning_language_id' => $language->id,
        'current_part_id' => $part->id,
        'current_level_id' => $level->id,
        'ability_level' => 'beginner',
        'onboarding_completed_at' => now(),
        'settings' => [],
    ]);

    UserLevelProgress::create([
        'user_id' => $user->id,
        'learning_level_id' => $level->id,
        'status' => 'available',
    ]);

    return compact('language', 'part', 'level', 'nextLevel', 'question');
}

it('allows authenticated users to open the premium page', function () {
    $user = User::factory()->create();

    PremiumPackage::create([
        'name' => 'Premium Bulanan',
        'slug' => 'premium-bulanan',
        'price' => 25000,
        'duration_days' => 30,
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('learning.premium'))
        ->assertOk()
        ->assertSee('YoLearning Premium');
});

it('activates premium for 30 days after manual payment approval', function () {
    $user = User::factory()->create();
    $package = PremiumPackage::create([
        'name' => 'Premium Bulanan',
        'slug' => 'premium-bulanan',
        'price' => 25000,
        'duration_days' => 30,
        'is_active' => true,
    ]);
    $payment = PremiumPayment::create([
        'user_id' => $user->id,
        'premium_package_id' => $package->id,
        'payment_code' => 'PRM-TEST-1',
        'payment_method' => 'manual_bank_transfer',
        'amount' => 25000,
        'payment_status' => PremiumPayment::STATUS_PENDING,
    ]);

    app(PremiumActivationService::class)->approve($payment);

    expect($payment->fresh()->payment_status)->toBe(PremiumPayment::STATUS_APPROVED);
    expect($user->fresh()->isPremium())->toBeTrue();
    expect($user->premiums()->first()->ends_at->isAfter(now()->addDays(29)))->toBeTrue();
});

it('shows level ads for free users and bypasses them for premium users', function () {
    $freeUser = User::factory()->create();
    $premiumUser = User::factory()->create();
    $freeCourse = yoLearningCourseFixture($freeUser);
    $premiumCourse = yoLearningCourseFixture($premiumUser);

    Ad::create(['title' => 'Entry Ad', 'placement' => 'level_entry', 'is_active' => true, 'duration_seconds' => 15]);
    Ad::create(['title' => 'Exit Ad', 'placement' => 'level_exit', 'is_active' => true, 'duration_seconds' => 15]);

    UserPremium::create([
        'user_id' => $premiumUser->id,
        'starts_at' => now()->subMinute(),
        'ends_at' => now()->addDays(30),
        'status' => 'active',
    ]);

    $this->actingAs($freeUser)
        ->get(route('learning.levels.show', [$freeCourse['part'], $freeCourse['level']]))
        ->assertOk()
        ->assertSee('data-show-ads="true"', false);

    $this->actingAs($premiumUser)
        ->get(route('learning.levels.show', [$premiumCourse['part'], $premiumCourse['level']]))
        ->assertOk()
        ->assertSee('data-show-ads="false"', false)
        ->assertDontSee('data-level-ad', false);
});

it('completes a level and stores per-question progress', function () {
    $user = User::factory()->create();
    $course = yoLearningCourseFixture($user);

    $this->actingAs($user)
        ->post(route('learning.levels.complete', [$course['part'], $course['level']]), [
            'study_seconds' => 65,
            'correct_count' => 1,
            'total_questions' => 1,
            'question_results' => json_encode([[
                'question_id' => $course['question']->id,
                'is_correct' => true,
                'selected_answer' => 'Halo',
                'attempts' => 1,
            ]]),
        ])
        ->assertRedirect(route('learning.parts.show', $course['part']));

    expect(UserQuestionProgress::where('user_id', $user->id)->where('learning_question_id', $course['question']->id)->exists())->toBeTrue();
    expect(UserLevelProgress::where('user_id', $user->id)->where('learning_level_id', $course['level']->id)->first()->status)->toBe('completed');
    expect(UserLevelProgress::where('user_id', $user->id)->where('learning_level_id', $course['nextLevel']->id)->first()->status)->toBe('available');
});

it('activates premium from a fake Midtrans settlement notification', function () {
    config(['services.midtrans.server_key' => 'midtrans-secret']);

    $user = User::factory()->create();
    $package = PremiumPackage::create([
        'name' => 'Premium Bulanan',
        'slug' => 'premium-bulanan',
        'price' => 25000,
        'duration_days' => 30,
        'is_active' => true,
    ]);
    $payment = PremiumPayment::create([
        'user_id' => $user->id,
        'premium_package_id' => $package->id,
        'payment_code' => 'PRM-MIDTRANS-1',
        'payment_method' => 'midtrans_snap',
        'amount' => 25000,
        'payment_status' => PremiumPayment::STATUS_PENDING,
        'gateway' => 'midtrans',
        'gateway_order_id' => 'PRM-MIDTRANS-1',
    ]);
    $payload = [
        'order_id' => $payment->gateway_order_id,
        'status_code' => '200',
        'gross_amount' => '25000.00',
        'transaction_status' => 'settlement',
        'transaction_id' => 'trx-test-1',
    ];
    $payload['signature_key'] = hash('sha512', $payload['order_id'] . $payload['status_code'] . $payload['gross_amount'] . 'midtrans-secret');

    $this->postJson(route('api.midtrans.premium.notification'), $payload)
        ->assertOk()
        ->assertJson(['status' => PremiumPayment::STATUS_APPROVED]);

    expect($user->fresh()->isPremium())->toBeTrue();

    $this->postJson(route('api.midtrans.premium.notification'), $payload)
        ->assertOk()
        ->assertJson(['status' => PremiumPayment::STATUS_APPROVED]);

    expect(UserPremium::where('user_id', $user->id)->where('premium_payment_id', $payment->id)->count())->toBe(1);
});

it('activates premium from a Midtrans capture notification without fraud status', function () {
    config(['services.midtrans.server_key' => 'midtrans-secret']);

    $user = User::factory()->create();
    $package = PremiumPackage::create([
        'name' => 'Premium Bulanan',
        'slug' => 'premium-capture',
        'price' => 25000,
        'duration_days' => 30,
        'is_active' => true,
    ]);
    $payment = PremiumPayment::create([
        'user_id' => $user->id,
        'premium_package_id' => $package->id,
        'payment_code' => 'PRM-MIDTRANS-CAPTURE',
        'payment_method' => 'midtrans_snap',
        'amount' => 25000,
        'payment_status' => PremiumPayment::STATUS_PENDING,
        'gateway' => 'midtrans',
        'gateway_order_id' => 'PRM-MIDTRANS-CAPTURE',
    ]);
    $payload = [
        'order_id' => $payment->gateway_order_id,
        'status_code' => '200',
        'gross_amount' => '25000.00',
        'transaction_status' => 'capture',
        'transaction_id' => 'trx-test-capture',
    ];
    $payload['signature_key'] = hash('sha512', $payload['order_id'] . $payload['status_code'] . $payload['gross_amount'] . 'midtrans-secret');

    $this->postJson(route('api.midtrans.premium.notification'), $payload)
        ->assertOk()
        ->assertJson(['status' => PremiumPayment::STATUS_APPROVED]);

    expect($user->fresh()->isPremium())->toBeTrue();
});

it('rejects Midtrans notifications when gross amount does not match payment amount', function () {
    config(['services.midtrans.server_key' => 'midtrans-secret']);

    $user = User::factory()->create();
    $package = PremiumPackage::create([
        'name' => 'Premium Bulanan',
        'slug' => 'premium-invalid-amount',
        'price' => 25000,
        'duration_days' => 30,
        'is_active' => true,
    ]);
    $payment = PremiumPayment::create([
        'user_id' => $user->id,
        'premium_package_id' => $package->id,
        'payment_code' => 'PRM-MIDTRANS-AMOUNT',
        'payment_method' => 'midtrans_snap',
        'amount' => 25000,
        'payment_status' => PremiumPayment::STATUS_PENDING,
        'gateway' => 'midtrans',
        'gateway_order_id' => 'PRM-MIDTRANS-AMOUNT',
    ]);
    $payload = [
        'order_id' => $payment->gateway_order_id,
        'status_code' => '200',
        'gross_amount' => '1000.00',
        'transaction_status' => 'settlement',
        'transaction_id' => 'trx-test-invalid-amount',
    ];
    $payload['signature_key'] = hash('sha512', $payload['order_id'] . $payload['status_code'] . $payload['gross_amount'] . 'midtrans-secret');

    $this->postJson(route('api.midtrans.premium.notification'), $payload)
        ->assertForbidden()
        ->assertJson(['message' => 'Nominal pembayaran Midtrans tidak sesuai.']);

    expect($payment->fresh()->payment_status)->toBe(PremiumPayment::STATUS_PENDING);
    expect($user->fresh()->isPremium())->toBeFalse();
});

it('stores quiz room history and awards quiz scores', function () {
    $owner = User::factory()->create();
    $player = User::factory()->create();
    $language = LearningLanguage::create(['name' => 'English', 'slug' => 'english', 'is_active' => true]);
    $room = QuizRoom::create([
        'learning_language_id' => $language->id,
        'owner_id' => $owner->id,
        'code' => 'ROOM01',
        'title' => 'Quick Quiz',
        'status' => 'playing',
        'started_at' => now(),
    ]);
    $question = QuizRoomQuestion::create([
        'quiz_room_id' => $room->id,
        'question_order' => 1,
        'question_text' => 'Hello berarti?',
        'seconds_limit' => 20,
        'points' => 100,
    ]);
    $option = QuizRoomOption::create([
        'quiz_room_question_id' => $question->id,
        'answer_text' => 'Halo',
        'is_correct' => true,
        'sort_order' => 1,
    ]);
    QuizRoomMember::create(['quiz_room_id' => $room->id, 'user_id' => $owner->id, 'joined_at' => now()]);
    QuizRoomMember::create(['quiz_room_id' => $room->id, 'user_id' => $player->id, 'joined_at' => now()]);

    $this->actingAs($player)
        ->postJson(route('api.quiz.answer', $room), [
            'question_id' => $question->id,
            'option_id' => $option->id,
            'answer_time_ms' => 1000,
        ])
        ->assertOk()
        ->assertJson(['is_correct' => true]);

    expect(QuizRoomMember::where('quiz_room_id', $room->id)->where('user_id', $player->id)->first()->score)->toBeGreaterThan(0);

    $this->actingAs($owner)
        ->post(route('learning.quiz.finish', $room))
        ->assertRedirect(route('learning.quiz.room', $room));

    expect(QuizRoomHistory::where('quiz_room_id', $room->id)->where('user_id', $player->id)->exists())->toBeTrue();
});

it('awards duel score for a correct answer', function () {
    $playerOne = User::factory()->create();
    $playerTwo = User::factory()->create();
    $language = LearningLanguage::create(['name' => 'English', 'slug' => 'english', 'is_active' => true]);
    $session = DuelSession::create([
        'code' => 'DUEL01',
        'learning_language_id' => $language->id,
        'player_one_id' => $playerOne->id,
        'player_two_id' => $playerTwo->id,
        'question_count' => 1,
        'seconds_per_question' => 10,
        'status' => 'active',
        'started_at' => now(),
    ]);
    $question = DuelQuestion::create([
        'duel_session_id' => $session->id,
        'question_order' => 1,
        'question_type' => 'multiple_choice',
        'question_text' => 'Hello berarti?',
        'options' => ['Halo', 'Pagi'],
        'correct_answer' => 'Halo',
    ]);
    DuelPlayer::create(['duel_session_id' => $session->id, 'user_id' => $playerOne->id, 'joined_at' => now()]);
    DuelPlayer::create(['duel_session_id' => $session->id, 'user_id' => $playerTwo->id, 'joined_at' => now()]);

    $this->actingAs($playerOne)
        ->postJson(route('api.duel.answer', $session), [
            'question_id' => $question->id,
            'selected_answer' => 'Halo',
            'answer_time_ms' => 1000,
        ])
        ->assertOk()
        ->assertJson(['is_correct' => true]);

    expect(DuelPlayer::where('duel_session_id', $session->id)->where('user_id', $playerOne->id)->first()->score)->toBeGreaterThan(0);
});
