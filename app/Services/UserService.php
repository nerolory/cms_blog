<?php

namespace App\Services;

use App\DTO\AuthorOption;
use App\DTO\ProfileData;
use App\DTO\RegisterData;
use App\DTO\ResetPasswordCredentials;
use App\Enums\AccountStatus;
use App\Models\User;
use App\Repositories\Contracts\FileRepositoryContract;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Services\Contracts\MailSettingsServiceContract;
use App\Services\Contracts\UserServiceContract;
use App\Support\Auth\AuthAttemptDiagnostics;
use App\Support\Cache\CacheVersionManager;
use App\Support\Media\ImageProcessor;
use App\Support\Rbac\DefaultUserRoleAssigner;
use App\Support\Rbac\RoleProvisioner;
use App\Support\TypeCast;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

/**
 * Сервис user.
 *
 * @property-read UserRepositoryContract $userRepository
 * @property-read FileRepositoryContract $fileRepository
 * @property-read MailSettingsServiceContract $mailSettingsService
 * @property-read RoleProvisioner $roleProvisioner
 * @property-read AuthAttemptDiagnostics $authAttemptDiagnostics
 * @property-read ImageProcessor $imageProcessor
 * @property-read CacheVersionManager $cacheVersions

 * @property-read DefaultUserRoleAssigner $defaultUserRoleAssigner
 */
class UserService implements UserServiceContract
{
    public function __construct(protected UserRepositoryContract $userRepository,
        protected FileRepositoryContract $fileRepository, protected MailSettingsServiceContract $mailSettingsService,
        protected RoleProvisioner $roleProvisioner, protected AuthAttemptDiagnostics $authAttemptDiagnostics,
        protected ImageProcessor $imageProcessor, protected CacheVersionManager $cacheVersions,
        protected DefaultUserRoleAssigner $defaultUserRoleAssigner) {}

    /**
     * authenticate.

     *
     * @return bool
     */
    public function authenticate(string $email, string $password, bool $remember = false): bool
    {
        $credentials = ['email' => mb_strtolower(trim($email)), 'password' => $password];
        $result = Auth::attempt($credentials, $remember);
        if (! $result) {
            $this->authAttemptDiagnostics->logFailure($email, $password, $result);
        }

        return $result;
    }

    /**
     * Регистрирует сервисы контейнера.
     *
     * @param  RegisterData  $data  данные формы

     * @return User
     */
    public function register(RegisterData $data): User
    {
        if ($this->userRepository->findByEmail($data->email) !== null) {
            throw ValidationException::withMessages(['email' => [__('auth.validation.email_taken')]]);
        }
        $user = $this->userRepository->createFromRegister($data);
        $this->defaultUserRoleAssigner->assignIfMissing($user);

        return $user;
    }

    /**
     * Обновляет profile.
     *
     * @param  User  $user  пользователь
     * @param  ProfileData  $data  данные формы

     * @return User
     */
    public function updateProfile(User $user, ProfileData $data): User
    {
        $existing = $this->userRepository->findByEmail($data->email);
        if ($existing !== null && $existing->id !== $user->id) {
            throw ValidationException::withMessages(['email' => [__('auth.validation.email_taken')]]);
        }
        $emailChanged = mb_strtolower(trim($data->email)) !== mb_strtolower($user->email);
        $themeChanged = $user->theme !== $data->theme->value;
        $updatedUser = $this->userRepository->updateProfile($user, $data);
        if ($themeChanged) {
            $this->cacheVersions->bumpPreferences();
        }
        if ($emailChanged && $this->mailSettingsService->isEmailVerificationRequired()) {
            $updatedUser = $this->userRepository->setEmailVerifiedAt($updatedUser, null);
            $updatedUser->sendEmailVerificationNotification();
        }

        return $updatedUser;
    }

    /**
     * upload avatar.
     *
     * @param  User  $user  пользователь

     * @return User
     */
    public function uploadAvatar(User $user, UploadedFile $file): User
    {
        $previousPath = $user->avatar_path;
        $binary = $this->imageProcessor->processAvatar($file);
        $this->fileRepository->deletePublic($previousPath);
        $path = $this->fileRepository->storePublicBinary('avatars', $binary);
        $updatedUser = $this->userRepository->updateAvatarPath($user, $path);
        $this->cacheVersions->bumpMediaFor($path);
        $this->cacheVersions->bumpMediaFor($previousPath);

        return $updatedUser;
    }

    /**
     * Удаляет avatar.
     *
     * @param  User  $user  пользователь

     * @return User
     */
    public function deleteAvatar(User $user): User
    {
        $previousPath = $user->avatar_path;
        $this->fileRepository->deletePublic($previousPath);
        $updatedUser = $this->userRepository->updateAvatarPath($user, null);
        $this->cacheVersions->bumpMediaFor($previousPath);

        return $updatedUser;
    }

    /**
     * activate.
     *
     * @param  User  $user  пользователь

     * @return User
     */
    public function activate(User $user): User
    {
        $user = $this->userRepository->setEmailVerifiedAt($user, now());

        return $this->userRepository->setAccountStatus($user, AccountStatus::Active);
    }

    /**
     * deactivate.
     *
     * @param  User  $user  пользователь

     * @return User
     */
    public function deactivate(User $user): User
    {
        $user = $this->userRepository->setEmailVerifiedAt($user, null);

        return $this->userRepository->setAccountStatus($user, AccountStatus::Pending);
    }

    /**
     * suspend.
     *
     * @param  User  $user  пользователь

     * @return User
     */
    public function suspend(User $user): User
    {
        return $this->userRepository->setAccountStatus($user, AccountStatus::Suspended);
    }

    /**
     * send password reset link.

     *
     * @return string
     */
    public function sendPasswordResetLink(string $email): string
    {
        return Password::sendResetLink(['email' => mb_strtolower(trim($email))]);
    }

    /**
     * reset password.

     *
     * @return string
     */
    public function resetPassword(ResetPasswordCredentials $credentials): string
    {
        return TypeCast::string(Password::reset($credentials->toPasswordBrokerPayload(), function (User $user,
            string $password): void {
            $this->userRepository->updatePassword($user, Hash::make($password));
        }));
    }

    /**
     * {@inheritdoc}
     *
     * @return Collection<int, AuthorOption>
     */
    public function authorOptionsFor(?int $includeUserId = null): Collection
    {
        return $this->userRepository->authorOptionsFor($includeUserId);
    }
}
