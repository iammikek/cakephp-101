<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Exception\ApiException;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class UsersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('users');
        $this->setPrimaryKey('id');
    }

    public function validationDefault(Validator $validator): Validator
    {
        return $validator;
    }

    public function register(string $email, string $password): \Cake\Datasource\EntityInterface
    {
        $existing = $this->find()->where(['email' => $email])->first();
        if ($existing !== null) {
            throw new ApiException(
                "User email '{$email}' already exists",
                409,
                'USER_EMAIL_EXISTS',
            );
        }

        $user = $this->newEntity([
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
        ]);
        $this->saveOrFail($user);

        return $user;
    }

    public function authenticate(string $email, string $password): ?\Cake\Datasource\EntityInterface
    {
        $user = $this->find()->where(['email' => $email])->first();
        if ($user === null || !password_verify($password, (string)$user->password)) {
            return null;
        }

        return $user;
    }
}
