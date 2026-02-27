<?php

namespace Feature\Api\Http\Controller;

use App\Dtos\Transaction\DepositData;
use App\Dtos\Transaction\ReversalData;
use App\Dtos\Transaction\TransferData;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $migrate = true;
    protected $seed = true;
    protected User $user;
    protected Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Accept', 'application/json');

        $this->user    = User::factory()->create();
        $this->account = Account::factory()->create(['available_balance' => 200]);

        UserAccount::factory()
            ->for($this->user)
            ->for($this->account)
            ->create();
    }

    protected const TRANSACTION_DATA_STRUCTURE = [
        'id',
        'type',
        'status',
        'amount',
        'origin_account',
        'destination_account',
        'user_id'
    ];

    #[Test]
    public function itThrowWhenUserIsNotLogged()
    {
        $this->post('api/transfer')->assertStatus(401);
        $this->post('api/deposit')->assertStatus(401);
        $this->post('api/reversal')->assertStatus(401);
        $this->get('api/transactions')->assertStatus(401);
    }

    #[Test]
    public function itCreatesDepositSuccessful(): void
    {
        $this->actingAs($this->user);

        $payload = DepositData::validateAndCreate([
            'amount'                 => 100,
            'destination_account_id' => $this->account->id
        ]);

        $response = $this->post('/api/deposit', $payload->toArray());

        $response->assertJsonStructure([
            'id',
            'type',
            'status',
            'amount',
            'account',
            'user_id'
        ])->assertStatus(201);

        $transactionId = $response->json()['id'];

        $this->assertDatabaseHas('accounts', [
            'id'                => $this->account->id,
            'available_balance' => $payload->amount + $this->account->available_balance,
        ]);

        $this->assertDatabaseHas('transactions', [
            'id'                     => $transactionId,
            'type'                   => TransactionType::DEPOSIT->value,
            'status'                 => TransactionStatus::FINISHED->value,
            'amount'                 => $payload->amount,
            'destination_account_id' => $payload->destination_account_id,
            'user_id'                => $this->user->id
        ]);

        $this->assertDatabaseHas('extracts', [
            'description'    => 'Deposit successfully created.',
            'transaction_id' => $transactionId,
            'user_id'        => $this->user->id,
        ]);
    }

    #[Test]
    public function itCreatesDepositWhenAccountHasNegativeBalance(): void
    {
        $this->actingAs($this->user);

        $this->account->available_balance -= 100;
        $this->account->save();

        $payload = DepositData::validateAndCreate([
            'amount'                 => 100,
            'destination_account_id' => $this->account->id
        ]);

        $response = $this->post('/api/deposit', $payload->toArray());

        $response->assertJsonStructure([
            'id',
            'type',
            'status',
            'amount',
            'account',
            'user_id'
        ])->assertStatus(201);

        $transactionId = $response->json()['id'];

        $this->assertDatabaseHas('accounts', [
            'id'                => $this->account->id,
            'available_balance' => $payload->amount + $this->account->available_balance,
        ]);

        $this->assertDatabaseHas('transactions', [
            'id'                     => $transactionId,
            'type'                   => TransactionType::DEPOSIT->value,
            'status'                 => TransactionStatus::FINISHED->value,
            'amount'                 => $payload->amount,
            'destination_account_id' => $payload->destination_account_id,
            'user_id'                => $this->user->id
        ]);

        $this->assertDatabaseHas('extracts', [
            'description'    => 'Deposit successfully created.',
            'transaction_id' => $transactionId,
            'user_id'        => $this->user->id,
        ]);
    }

    #[Test]
    public function itCreatesTransferSuccessful(): void
    {
        $this->actingAs($this->user);

        $destination_account = Account::factory()->create();

        $payload = TransferData::validateAndCreate([
            'amount'                 => 50,
            'origin_account_id'      => $this->account->id,
            'destination_account_id' => $destination_account->id
        ]);

        $response = $this->post('/api/transfer', $payload->toArray());

        $response->assertJsonStructure(self::TRANSACTION_DATA_STRUCTURE)
            ->assertStatus(201);

        $transactionId = $response->json()['id'];

        $this->assertDatabaseHas('accounts', [
            'id'                => $this->account->id,
            'available_balance' => $this->account->available_balance - $payload->amount,
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'                => $destination_account->id,
            'available_balance' => $payload->amount + $destination_account->available_balance,
        ]);

        $this->assertDatabaseHas('transactions', [
            'id'                     => $transactionId,
            'type'                   => TransactionType::TRANSFER->value,
            'status'                 => TransactionStatus::FINISHED->value,
            'amount'                 => $payload->amount,
            'origin_account_id'      => $this->account->id,
            'destination_account_id' => $payload->destination_account_id,
            'user_id'                => $this->user->id
        ]);

        $this->assertDatabaseHas('extracts', [
            'description'    => 'Transfer successfully created.',
            'transaction_id' => $transactionId,
            'user_id'        => $this->user->id,
        ]);
    }

    #[Test]
    public function itCreatesTransferSuccessfulWhenAccountHasNegativeBalance(): void
    {
        $this->actingAs($this->user);

        $destination_account = Account::factory()->create(['available_balance' => -20]);

        $payload = TransferData::validateAndCreate([
            'amount'                 => 50,
            'origin_account_id'      => $this->account->id,
            'destination_account_id' => $destination_account->id
        ]);

        $response = $this->post('/api/transfer', $payload->toArray());

        $response->assertJsonStructure(self::TRANSACTION_DATA_STRUCTURE)
            ->assertStatus(201);

        $transactionId = $response->json()['id'];

        $this->assertDatabaseHas('accounts', [
            'id'                => $this->account->id,
            'available_balance' => $this->account->available_balance - $payload->amount,
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'                => $destination_account->id,
            'available_balance' => $payload->amount + $destination_account->available_balance,
        ]);

        $this->assertDatabaseHas('transactions', [
            'id'                     => $transactionId,
            'type'                   => TransactionType::TRANSFER->value,
            'status'                 => TransactionStatus::FINISHED->value,
            'amount'                 => $payload->amount,
            'origin_account_id'      => $this->account->id,
            'destination_account_id' => $payload->destination_account_id,
            'user_id'                => $this->user->id
        ]);

        $this->assertDatabaseHas('extracts', [
            'description'    => 'Transfer successfully created.',
            'transaction_id' => $transactionId,
            'user_id'        => $this->user->id,
        ]);
    }

    #[Test]
    public function itReversalADepositTransaction(): void
    {
        $this->actingAs($this->user);

        $transaction = Transaction::factory()->create([
            'type'                   => TransactionType::DEPOSIT->value,
            'status'                 => TransactionStatus::FINISHED->value,
            'amount'                 => 50,
            'destination_account_id' => $this->account->id,
            'user_id'                => $this->user->id,
        ]);

        $payload = ReversalData::validateAndCreate([
            'transaction_id' => $transaction->id
        ]);

        $this->post('/api/reversal', $payload->toArray())
            ->assertJsonStructure(self::TRANSACTION_DATA_STRUCTURE)
            ->assertStatus(201);

        $this->assertDatabaseHas('accounts', [
            'id'                => $this->account->id,
            'available_balance' => $this->account->available_balance - $transaction->amount,
        ]);

        $this->assertDatabaseHas('transactions', [
            'id'                     => $transaction->id,
            'type'                   => TransactionType::DEPOSIT->value,
            'status'                 => TransactionStatus::CHARGEBACK->value,
            'amount'                 => $transaction->amount,
            'origin_account_id'      => null,
            'destination_account_id' => $this->account->id,
            'user_id'                => $this->user->id
        ]);

        $this->assertDatabaseHas('extracts', [
            'description'    => 'Deposit successfully returned.',
            'transaction_id' => $transaction->id,
            'user_id'        => $this->user->id,
        ]);
    }

    #[Test]
    public function itReversalATransferTransaction(): void
    {
        $this->actingAs($this->user);

        $destination_account = Account::factory()->create();

        $transaction = Transaction::factory()->create([
            'type'                   => TransactionType::TRANSFER->value,
            'status'                 => TransactionStatus::FINISHED->value,
            'amount'                 => 50,
            'origin_account_id'      => $this->account->id,
            'destination_account_id' => $destination_account->id,
            'user_id'                => $this->user->id,
        ]);

        $payload = ReversalData::validateAndCreate([
            'transaction_id' => $transaction->id
        ]);

        $this->post('/api/reversal', $payload->toArray())
            ->assertJsonStructure(self::TRANSACTION_DATA_STRUCTURE)
            ->assertStatus(201);

        $this->assertDatabaseHas('accounts', [
            'id'                => $this->account->id,
            'available_balance' => $this->account->available_balance + $transaction->amount,
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'                => $destination_account->id,
            'available_balance' => $destination_account->available_balance - $transaction->amount,
        ]);

        $this->assertDatabaseHas('transactions', [
            'id'                     => $transaction->id,
            'type'                   => TransactionType::TRANSFER->value,
            'status'                 => TransactionStatus::CHARGEBACK->value,
            'amount'                 => $transaction->amount,
            'origin_account_id'      => $this->account->id,
            'destination_account_id' => $destination_account->id,
            'user_id'                => $this->user->id
        ]);

        $this->assertDatabaseHas('extracts', [
            'description'    => 'Transfer successfully returned.',
            'transaction_id' => $transaction->id,
            'user_id'        => $this->user->id,
        ]);
    }

    #[Test]
    public function itGetAllTransactionsForLoggedUser(): void
    {
        $this->actingAs($this->user);

        Transaction::factory()->create([
            'type'                   => TransactionType::DEPOSIT->value,
            'status'                 => TransactionStatus::FINISHED->value,
            'amount'                 => 50,
            'destination_account_id' => $this->account->id,
            'user_id'                => $this->user->id,
        ]);

        $destination_account = Account::factory()->create();

        Transaction::factory()->create([
            'type'                   => TransactionType::TRANSFER->value,
            'status'                 => TransactionStatus::FINISHED->value,
            'amount'                 => 50,
            'origin_account_id'      => $this->account->id,
            'destination_account_id' => $destination_account->id,
            'user_id'                => $this->user->id,
        ]);

        $this->get('/api/transactions')
            ->assertJsonStructure(['*' => self::TRANSACTION_DATA_STRUCTURE])
            ->assertStatus(201);
    }

    #[Test]
    public function itThrowsWhenTryReversalAChargebackTransaction(): void
    {
        $this->actingAs($this->user);

        $transaction = Transaction::factory()->create([
            'type'                   => TransactionType::DEPOSIT->value,
            'status'                 => TransactionStatus::CHARGEBACK->value,
            'amount'                 => 50,
            'destination_account_id' => $this->account->id,
            'user_id'                => $this->user->id,
        ]);

        $payload = ReversalData::validateAndCreate([
            'transaction_id' => $transaction->id
        ]);

        $this->post('/api/reversal', $payload->toArray())
            ->assertJsonFragment(['Transaction has already been refunded.'])
            ->assertStatus(500);

        $this->assertDatabaseHas('accounts', [
            'id'                => $this->account->id,
            'available_balance' => $this->account->available_balance,
        ]);

        $this->assertDatabaseHas('transactions', [
            'id'                     => $transaction->id,
            'type'                   => TransactionType::DEPOSIT->value,
            'status'                 => TransactionStatus::CHARGEBACK->value,
            'amount'                 => $transaction->amount,
            'origin_account_id'      => null,
            'destination_account_id' => $this->account->id,
            'user_id'                => $this->user->id
        ]);

        $this->assertDatabaseEmpty('extracts');
    }

    #[Test]
    public function itThrowsWhenTryReversalAUnknownTransactionType(): void
    {
        $this->actingAs($this->user);

        $transaction = Transaction::factory()->create([
            'type'                   => 'UNKNOWN',
            'status'                 => TransactionStatus::FINISHED->value,
            'amount'                 => 50,
            'destination_account_id' => $this->account->id,
            'user_id'                => $this->user->id,
        ]);

        $payload = ReversalData::validateAndCreate([
            'transaction_id' => $transaction->id
        ]);

        $this->post('/api/reversal', $payload->toArray())
            ->assertJsonFragment(['This transaction is not refundable.'])
            ->assertStatus(500);

        $this->assertDatabaseHas('accounts', [
            'id'                => $this->account->id,
            'available_balance' => $this->account->available_balance,
        ]);

        $this->assertDatabaseHas('transactions', [
            'id'                     => $transaction->id,
            'type'                   => 'UNKNOWN',
            'status'                 => TransactionStatus::FINISHED->value,
            'amount'                 => $transaction->amount,
            'origin_account_id'      => null,
            'destination_account_id' => $this->account->id,
            'user_id'                => $this->user->id
        ]);

        $this->assertDatabaseEmpty('extracts');
    }

    #[Test]
    public function itThrowsInDepositWhenAccountIsInexistent(): void
    {
        $this->actingAs($this->user);

        $payload = DepositData::validateAndCreate([
            'amount'                 => 100,
            'destination_account_id' => 1234
        ]);

        $this->post('/api/deposit', $payload->toArray())
            ->assertNotFound();

        $this->assertDatabaseHas('accounts', [
            'id'                => $this->account->id,
            'available_balance' => $this->account->available_balance,
        ]);

        $this->assertDatabaseEmpty('transactions');

        $this->assertDatabaseEmpty('extracts');
    }

    #[Test]
    public function itThrowsWhenAccountHasInsufficientBalance(): void
    {
        $this->actingAs($this->user);

        $destination_account = Account::factory()->create();
        $destination_account->refresh();

        $payload = TransferData::validateAndCreate([
            'amount'                 => 500,
            'origin_account_id'      => $this->account->id,
            'destination_account_id' => $destination_account->id
        ]);

        $this->post('/api/transfer', $payload->toArray())
            ->assertJson([
                "message" => "Insufficient balance",
                "errors"  => [
                    "account" => ["Insufficient balance"]
                ]
            ])->assertStatus(422);

        $this->assertDatabaseHas('accounts', [
            'id'                => $this->account->id,
            'available_balance' => $this->account->available_balance,
        ]);

        $this->assertDatabaseHas('accounts', [
            'id'                => $destination_account->id,
            'available_balance' => $destination_account->available_balance,
        ]);

        $this->assertDatabaseEmpty('transactions');

        $this->assertDatabaseEmpty('extracts');
    }

    #[Test]
    public function itThrowsWhenAccountIsInexistent(): void
    {
        $this->actingAs($this->user);

        $destination_account = 1234;

        $payload = TransferData::validateAndCreate([
            'amount'                 => 50,
            'origin_account_id'      => $this->account->id,
            'destination_account_id' => $destination_account
        ]);

        $this->post('/api/transfer', $payload->toArray())
            ->assertNotFound();

        $this->assertDatabaseHas('accounts', [
            'id'                => $this->account->id,
            'available_balance' => $this->account->available_balance,
        ]);

        $this->assertDatabaseEmpty('transactions');

        $this->assertDatabaseEmpty('extracts');
    }
}
