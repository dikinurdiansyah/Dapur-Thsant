<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    protected $signature = 'admin:create
                            {--name= : Nama lengkap admin}
                            {--email= : Email untuk login}
                            {--password= : Password (minimal 8 karakter)}
                            {--role=OWNER : ADMIN atau OWNER}
                            {--phone= : Nomor telepon (opsional)}';

    protected $description = 'Membuat akun admin/owner (seeder tidak membuat akun di production)';

    public function handle(): int
    {
        $name = $this->option('name');
        $email = $this->option('email');
        $password = $this->option('password');
        $phone = $this->option('phone');

        if (! $this->input->isInteractive() && (! $name || ! $email || ! $password)) {
            $this->error('Mode non-interaktif butuh --name, --email, dan --password.');

            return self::FAILURE;
        }

        if ($phone === null && $this->input->isInteractive()) {
            $phone = $this->ask('Nomor telepon (opsional, tekan Enter untuk lewati)');
        }

        $attributes = [
            'name' => trim((string) ($name ?: $this->ask('Nama lengkap admin'))),
            'email' => strtolower(trim((string) ($email ?: $this->ask('Email login')))),
            'password' => (string) ($password ?: $this->secret('Password (minimal 8 karakter)')),
            'role' => strtoupper((string) $this->option('role')),
            'phone_number' => $phone ?: null,
        ];

        $validator = Validator::make($attributes, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:ADMIN,OWNER',
            'phone_number' => 'nullable|string|max:20',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email tersebut sudah dipakai akun lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal :min karakter.',
            'role.in' => 'Role harus ADMIN atau OWNER.',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'password_hash' => Hash::make($attributes['password']),
            'role' => $attributes['role'],
            'phone_number' => $attributes['phone_number'],
        ]);

        $this->info("Akun {$user->role} dibuat: {$user->email}");

        return self::SUCCESS;
    }
}
