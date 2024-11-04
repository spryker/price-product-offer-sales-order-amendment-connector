<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\MerchantUser\Business;

use Codeception\Test\Unit;
use Generated\Shared\DataBuilder\UserBuilder;
use Generated\Shared\Transfer\MerchantTransfer;
use Generated\Shared\Transfer\MerchantUserCriteriaTransfer;
use Generated\Shared\Transfer\MerchantUserTransfer;
use Generated\Shared\Transfer\UserCollectionTransfer;
use Generated\Shared\Transfer\UserCriteriaTransfer;
use Generated\Shared\Transfer\UserPasswordResetRequestTransfer;
use Generated\Shared\Transfer\UserTransfer;
use Orm\Zed\MerchantUser\Persistence\SpyMerchantUser;
use Spryker\Zed\MerchantUser\Dependency\Facade\MerchantUserToUserFacadeInterface;
use Spryker\Zed\MerchantUser\Dependency\Facade\MerchantUserToUserPasswordResetFacadeInterface;
use Spryker\Zed\MerchantUser\MerchantUserDependencyProvider;
use Spryker\Zed\MerchantUserExtension\Dependency\Plugin\MerchantUserRoleFilterPreConditionPluginInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group MerchantUser
 * @group Business
 * @group Facade
 * @group MerchantUserFacadeTest
 *
 * Add your own group annotations below this line
 */
class MerchantUserFacadeTest extends Unit
{
    /**
     * @see \Orm\Zed\User\Persistence\Map\SpyUserTableMap::COL_STATUS_BLOCKED
     *
     * @var string
     */
    protected const USER_STATUS_BLOCKED = 'blocked';

    /**
     * @var string
     */
    protected const USER_AUTHENTICATION_TOKEN = 'token';

    /**
     * @var string
     */
    protected const USER_PASSWORD = 'password';

    /**
     * @var \Generated\Shared\Transfer\MerchantUserTransfer
     */
    protected $merchantUserTransfer;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\User\Business\UserFacadeInterface
     */
    protected $userFacadeMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\UserPasswordReset\Business\UserPasswordResetFacadeInterface
     */
    protected $userPasswordResetFacadeMock;

    /**
     * @var \SprykerTest\Zed\MerchantUser\MerchantUserBusinessTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->userPasswordResetFacadeMock = $this->getMockBuilder(MerchantUserToUserPasswordResetFacadeInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['requestPasswordReset', 'isValidPasswordResetToken', 'setNewPassword'])
            ->getMockForAbstractClass();

        $this->userFacadeMock = $this->getMockBuilder(MerchantUserToUserFacadeInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'updateUser',
                'createUser',
                'getCurrentUser',
                'setCurrentUser',
                'isValidPassword',
                'getUserCollection',
            ])
            ->getMockForAbstractClass();
    }

    /**
     * @return void
     */
    public function testCreateReturnsTrueIfUserDoesNotExist(): void
    {
        // Arrange
        $userTransfer = (new UserBuilder())->build();
        $merchantTransfer = $this->tester->haveMerchant();
        $merchantUserTransfer = new MerchantUserTransfer();
        $merchantUserTransfer->setIdMerchant($merchantTransfer->getIdMerchant())->setUser($userTransfer);

        // Act
        $merchantUserResponseTransfer = $this->tester->getFacade()->createMerchantUser($merchantUserTransfer);
        $merchantUserEntity = $this->tester->findMerchantUser(
            (new MerchantUserCriteriaTransfer())->setIdMerchantUser($merchantUserTransfer->getIdMerchantUser()),
        );

        // Assert
        $this->assertTrue($merchantUserResponseTransfer->getIsSuccessful());
        $this->assertInstanceOf(SpyMerchantUser::class, $merchantUserEntity);
    }

    /**
     * @return void
     */
    public function testCreateReturnsTrueIfUserExist(): void
    {
        // Arrange
        $userTransfer = (new UserBuilder())->build();
        $merchantTransfer = $this->tester->haveMerchant([MerchantTransfer::EMAIL => 'test_merchant_1@spryker.com']);
        $merchantUserTransfer = new MerchantUserTransfer();

        $merchantUserTransfer->setIdMerchant($merchantTransfer->getIdMerchant())
            ->setUser($userTransfer);

        // Act
        $merchantUserResponseTransfer = $this->tester->getFacade()->createMerchantUser($merchantUserTransfer);
        $merchantUserEntity = $this->tester->findMerchantUser(
            (new MerchantUserCriteriaTransfer())->setIdMerchantUser($merchantUserTransfer->getIdMerchantUser()),
        );

        // Assert
        $this->assertTrue($merchantUserResponseTransfer->getIsSuccessful());
        $this->assertInstanceOf(SpyMerchantUser::class, $merchantUserEntity);
    }

    /**
     * @return void
     */
    public function testCreateReturnsFalseIfUserAlreadyHasMerchant(): void
    {
        // Arrange
        $newUserTransfer = (new UserBuilder())->build();
        $userTransfer = $this->tester->haveUser([
            UserTransfer::USERNAME => $newUserTransfer->getUsername(),
        ]);
        $merchantUserTransfer = new MerchantUserTransfer();

        $merchantOneTransfer = $this->tester->haveMerchant([MerchantTransfer::EMAIL => 'test_merchant_1@spryker.com']);
        $merchantTwoTransfer = $this->tester->haveMerchant([MerchantTransfer::EMAIL => 'test_merchant_2@spryker.com']);

        $this->tester->haveMerchantUser($merchantOneTransfer, $userTransfer);

        $merchantUserTransfer->setIdMerchant($merchantTwoTransfer->getIdMerchant())
            ->setUser($newUserTransfer);

        // Act
        $merchantUserResponseTransfer = $this->tester->getFacade()->createMerchantUser($merchantUserTransfer);

        // Assert
        $this->assertFalse($merchantUserResponseTransfer->getIsSuccessful());
        $this->assertSame(
            'A user with the same email is already connected to another merchant.',
            $merchantUserResponseTransfer->getErrors()[0]->getMessage(),
        );
    }

    /**
     * @return void
     */
    public function testUpdate(): void
    {
        // Arrange
        $this->initializeFacadeMocks();

        $userTransfer = $this->tester->haveUser([
            UserTransfer::USERNAME => 'test_merchant_user@spryker.com',
        ]);

        $merchantTransfer = $this->tester->haveMerchant([MerchantTransfer::EMAIL => 'test_merchant_1@spryker.com']);
        $merchantUserTransfer = $this->tester->haveMerchantUser($merchantTransfer, $userTransfer);
        $merchantUserTransfer->setUser($userTransfer);

        $this->userFacadeMock->expects($this->once())->method('getUserCollection')
            ->willReturn((new UserCollectionTransfer())->addUser($userTransfer));

        $this->userFacadeMock->expects($this->once())->method('updateUser')
            ->with($userTransfer)
            ->willReturn($userTransfer);

        // Act
        $merchantUserResponseTransfer = $this->tester->getFacade()->updateMerchantUser($merchantUserTransfer);

        // Assert
        $this->assertTrue($merchantUserResponseTransfer->getIsSuccessful());
    }

    /**
     * @return void
     */
    public function testUpdateWithNewActiveStatus(): void
    {
        // Arrange
        $newUserTransfer = (new UserBuilder())->build();
        $this->initializeFacadeMocks();

        $userTransfer = $this->tester->haveUser([
            UserTransfer::USERNAME => 'test_merchant_user@spryker.com',
        ])->setStatus('blocked');

        $merchantTransfer = $this->tester->haveMerchant([MerchantTransfer::EMAIL => 'test_merchant_1@spryker.com']);
        $merchantUserTransfer = $this->tester->haveMerchantUser($merchantTransfer, $userTransfer);
        $merchantUserTransfer->setUser($userTransfer);

        $this->userFacadeMock->expects($this->once())->method('getUserCollection')
            ->willReturn((new UserCollectionTransfer())->addUser($userTransfer));

        $this->userFacadeMock->expects($this->once())->method('updateUser')
            ->with($userTransfer)
            ->willReturn($newUserTransfer->setStatus('active'));

        $this->userPasswordResetFacadeMock->expects($this->once())->method('requestPasswordReset');

        // Act
        $merchantUserResponseTransfer = $this->tester->getFacade()->updateMerchantUser($merchantUserTransfer);

        // Assert
        $this->assertTrue($merchantUserResponseTransfer->getIsSuccessful());
    }

    /**
     * @return void
     */
    public function testUpdateWithNewBlockedStatus(): void
    {
        // Arrange
        $newUserTransfer = (new UserBuilder())->build();
        $this->initializeFacadeMocks();

        $userTransfer = $this->tester->haveUser([
            UserTransfer::USERNAME => 'test_merchant_user@spryker.com',
        ])->setStatus('blocked');

        $merchantTransfer = $this->tester->haveMerchant([MerchantTransfer::EMAIL => 'test_merchant_1@spryker.com']);
        $merchantUserTransfer = $this->tester->haveMerchantUser($merchantTransfer, $userTransfer);
        $merchantUserTransfer->setUser($userTransfer);

        $this->userFacadeMock->expects($this->once())->method('getUserCollection')
            ->willReturn((new UserCollectionTransfer())->addUser($userTransfer));

        $this->userFacadeMock->expects($this->once())->method('updateUser')
            ->with($userTransfer)
            ->willReturn($newUserTransfer->setStatus('active'));

        $this->userPasswordResetFacadeMock->expects($this->once())->method('requestPasswordReset');

        // Act
        $merchantUserResponseTransfer = $this->tester->getFacade()->updateMerchantUser($merchantUserTransfer);

        // Assert
        $this->assertTrue($merchantUserResponseTransfer->getIsSuccessful());
    }

    /**
     * @dataProvider getMerchantUserPositiveScenarioDataProvider
     *
     * @param array<string> $merchantUserCriteriaDataKeys
     * @param bool $isUserInCriteria
     *
     * @return void
     */
    public function testFindMerchantUserReturnsTransferWithCorrectCriteria(
        array $merchantUserCriteriaDataKeys,
        bool $isUserInCriteria
    ): void {
        // Arrange
        $userTransfer = $this->tester->haveUser([
            UserTransfer::USERNAME => 'test_merchant_user@spryker.com',
        ]);
        $merchantTransfer = $this->tester->haveMerchant([MerchantTransfer::EMAIL => 'test_merchant_1@spryker.com']);
        $merchantUserTransfer = $this->tester->haveMerchantUser($merchantTransfer, $userTransfer);

        $merchantUserCriteriaData = [
            MerchantUserCriteriaTransfer::ID_MERCHANT_USER => $merchantUserTransfer->getIdMerchantUser(),
            MerchantUserCriteriaTransfer::ID_MERCHANT => $merchantTransfer->getIdMerchant(),
            MerchantUserCriteriaTransfer::ID_USER => $userTransfer->getIdUser(),
            MerchantUserCriteriaTransfer::WITH_USER => true,
        ];
        $merchantUserCriteriaData = array_intersect_key(
            $merchantUserCriteriaData,
            array_flip($merchantUserCriteriaDataKeys),
        );

        $merchantUserCriteriaTransfer = (new MerchantUserCriteriaTransfer())
            ->fromArray($merchantUserCriteriaData);

        // Act
        $foundMerchantUserTransfer = $this->tester
            ->getFacade()
            ->findMerchantUser($merchantUserCriteriaTransfer);

        // Assert
        $this->assertSame(
            $merchantUserTransfer->getIdMerchantUser(),
            $foundMerchantUserTransfer->getIdMerchantUser(),
        );

        if ($isUserInCriteria) {
            $this->assertInstanceOf(UserTransfer::class, $foundMerchantUserTransfer->getUser());
        }
    }

    /**
     * @dataProvider getMerchantUserNegativeScenarioDataProvider
     *
     * @param array $merchantUserCriteriaData
     *
     * @return void
     */
    public function testFindMerchantUserReturnsNullWithWrongCriteria(
        array $merchantUserCriteriaData
    ): void {
        // Arrange
        $userTransfer = $this->tester->haveUser([
            UserTransfer::USERNAME => 'test_merchant_user@spryker.com',
        ]);
        $merchantTransfer = $this->tester->haveMerchant([MerchantTransfer::EMAIL => 'test_merchant_1@spryker.com']);
        $this->tester->haveMerchantUser($merchantTransfer, $userTransfer);

        $merchantUserCriteriaTransfer = (new MerchantUserCriteriaTransfer())
            ->fromArray($merchantUserCriteriaData);

        // Act
        $foundMerchantUserTransfer = $this->tester
            ->getFacade()
            ->findMerchantUser($merchantUserCriteriaTransfer);

        // Assert
        $this->assertNull($foundMerchantUserTransfer);
    }

    /**
     * @return void
     */
    public function testDisableMerchantUsersByMerchantId(): void
    {
        // Arrange
        $this->initializeFacadeMocks();

        $userOneTransfer = $this->tester->haveUser([
            UserTransfer::USERNAME => 'test_merchant_user@spryker_one.com',
        ]);

        $userTwoTransfer = $this->tester->haveUser([
            UserTransfer::USERNAME => 'test_merchant_user@spryker_two.com',
        ]);

        $merchantTransfer = $this->tester->haveMerchant([MerchantTransfer::EMAIL => 'test_merchant_1@spryker.com']);

        $this->tester->haveMerchantUser($merchantTransfer, $userOneTransfer);
        $this->tester->haveMerchantUser($merchantTransfer, $userTwoTransfer);

        $this->userFacadeMock->expects($this->exactly(2))->method('deactivateUser');

        // Act
        $this->tester->getFacade()->disableMerchantUsers(
            (new MerchantUserCriteriaTransfer())->setIdMerchant($merchantTransfer->getIdMerchant()),
        );
    }

    /**
     * @return void
     */
    public function testGetCurrentMerchantUserReturnsCorrectMerchantUser(): void
    {
        // Arrange
        $this->initializeFacadeMocks();

        $merchantTransfer = $this->tester->haveMerchant();
        $userTransfer = $this->tester->haveUser();
        $merchantUserTransfer = $this->tester->haveMerchantUser($merchantTransfer, $userTransfer);
        $merchantUserTransfer->setUser($userTransfer);
        $this->userFacadeMock->method('getCurrentUser')->willReturn($userTransfer);

        // Act
        $currentMerchantUserTransfer = $this->tester->getFacade()->getCurrentMerchantUser();

        // Assert
        $this->assertEquals($merchantUserTransfer, $currentMerchantUserTransfer);
    }

    /**
     * @return void
     */
    public function testAuthenticateMerchantUserMerchantUserCallUserFacade(): void
    {
        // Arrange
        $this->initializeFacadeMocks();
        $userTransfer = $this->tester->haveUser();
        $merchantUserTransfer = $this->tester->haveMerchantUser(
            $this->tester->haveMerchant(),
            $userTransfer,
        );

        // Assert
        $this->userFacadeMock->expects($this->once())->method('setCurrentUser');
        $this->userFacadeMock->expects($this->once())->method('updateUser');

        // Act
        $this->tester->getFacade()->authenticateMerchantUser($merchantUserTransfer->setUser($userTransfer));
    }

    /**
     * @return void
     */
    public function testFindUserForwardsToUserFacade(): void
    {
        // Arrange
        $this->initializeFacadeMocks();
        $userTransfer = $this->tester->haveUser();
        $this->userFacadeMock->method('getUserCollection')->willReturn((new UserCollectionTransfer()));

        // Assert
        $this->userFacadeMock->expects($this->once())->method('getUserCollection');

        // Act
        $this->tester->getFacade()->findUser((new UserCriteriaTransfer())->setIdUser($userTransfer->getIdUser()));
    }

    /**
     * @return void
     */
    public function testRequestPasswordResetForwardsToUserPasswordResetFacade(): void
    {
        // Arrange
        $this->initializeFacadeMocks();
        $userTransfer = $this->tester->haveUser();

        // Assert
        $this->userPasswordResetFacadeMock->expects($this->once())->method('requestPasswordReset');

        // Act
        $this->tester->getFacade()->requestPasswordReset(
            (new UserPasswordResetRequestTransfer())
                ->setEmail($userTransfer->getUsername()),
        );
    }

    /**
     * @return void
     */
    public function testIsValidPasswordResetTokenForwardsToUserPasswordResetFacade(): void
    {
        /// Arrange
        $this->initializeFacadeMocks();

        // Assert
        $this->userPasswordResetFacadeMock->expects($this->once())->method('isValidPasswordResetToken');

        // Act
        $this->tester->getFacade()->isValidPasswordResetToken(static::USER_AUTHENTICATION_TOKEN);
    }

    /**
     * @return void
     */
    public function testSetNewPasswordForwardsToUserPasswordResetFacade(): void
    {
        /// Arrange
        $this->initializeFacadeMocks();

        // Assert
        $this->userPasswordResetFacadeMock->expects($this->once())->method('setNewPassword');

        // Act
        $this->tester->getFacade()->setNewPassword(static::USER_AUTHENTICATION_TOKEN, static::USER_PASSWORD);
    }

    /**
     * @return void
     */
    public function testSetCurrentMerchantUserCallsUserFacade(): void
    {
        // Arrange
        $this->initializeFacadeMocks();

        $userTransfer = new UserTransfer();
        $merchantUserTransfer = (new MerchantUserTransfer())->setUser($userTransfer);

        // Check call is proxied to UserFacade
        $this->userFacadeMock->expects($this->once())->method('setCurrentUser')
            ->with($userTransfer)
            ->willReturn(null);

        // Act
        $this->tester->getFacade()->setCurrentMerchantUser($merchantUserTransfer);
    }

    /**
     * @return void
     */
    public function testIsValidPasswordCallsUserFacade(): void
    {
        // Arrange
        $this->initializeFacadeMocks();

        $password = 'foo';
        $hash = '$2y$10$y3HMfu3Dv0AyOlkILUt21O0mH3A3Tk0BPzUFqZab67zFpEMZIgx2K';

        // Check call is proxied to UserFacade
        $this->userFacadeMock->expects($this->once())->method('isValidPassword')
            ->with($password, $hash)
            ->willReturn(true);

        // Act
        $this->tester->getFacade()->isValidPassword($password, $hash);
    }

    /**
     * @return void
     */
    public function testFilterUserRolesWithMetPreconditions(): void
    {
        // Arrange
        $preConditionPlugin = $this->getMockBuilder(MerchantUserRoleFilterPreConditionPluginInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['checkCondition'])
            ->getMock();
        $preConditionPlugin->expects($this->once())->method('checkCondition')
            ->willReturn(true);

        $this->tester->setDependency(MerchantUserDependencyProvider::PLUGINS_MERCHANT_USER_ROLE_FILTER_PRE_CONDITION, function () use ($preConditionPlugin) {
            return [
                $preConditionPlugin,
            ];
        });

        $merchantTransfer = $this->tester->haveMerchant();
        $userTransfer = $this->tester->haveUser();
        $this->tester->haveMerchantUser($merchantTransfer, $userTransfer);

        // Act
        $roles = $this->tester->getFacade()->filterUserRoles(
            $userTransfer,
            ['ROLE_BACK_OFFICE_USER'],
        );

        // Assert
        $this->assertSame($roles, []);
    }

    /**
     * @return void
     */
    public function testFilterUserRolesWithUnmetPrecondition(): void
    {
        // Arrange
        $preConditionPlugin = $this->getMockBuilder(MerchantUserRoleFilterPreConditionPluginInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['checkCondition'])
            ->getMock();
        $preConditionPlugin->expects($this->once())->method('checkCondition')
            ->willReturn(false);

        $this->tester->setDependency(MerchantUserDependencyProvider::PLUGINS_MERCHANT_USER_ROLE_FILTER_PRE_CONDITION, function () use ($preConditionPlugin) {
            return [
                $preConditionPlugin,
            ];
        });

        $merchantTransfer = $this->tester->haveMerchant();
        $userTransfer = $this->tester->haveUser();
        $this->tester->haveMerchantUser($merchantTransfer, $userTransfer);

        // Act
        $roles = $this->tester->getFacade()->filterUserRoles(
            $userTransfer,
            ['ROLE_BACK_OFFICE_USER'],
        );

        // Assert
        $this->assertSame($roles, ['ROLE_BACK_OFFICE_USER']);
    }

    /**
     * @return void
     */
    public function testFilterUserRolesWithNoPreconditionPlugins(): void
    {
        // Arrange
        $merchantTransfer = $this->tester->haveMerchant();
        $userTransfer = $this->tester->haveUser();
        $this->tester->haveMerchantUser($merchantTransfer, $userTransfer);

        // Act
        $roles = $this->tester->getFacade()->filterUserRoles(
            $userTransfer,
            ['ROLE_BACK_OFFICE_USER'],
        );

        // Assert
        $this->assertSame($roles, []);
    }

    /**
     * @return void
     */
    protected function initializeFacadeMocks(): void
    {
        $this->tester->setDependency(
            MerchantUserDependencyProvider::FACADE_USER_PASSWORD_RESET,
            $this->userPasswordResetFacadeMock,
        );
        $this->tester->setDependency(
            MerchantUserDependencyProvider::FACADE_USER,
            $this->userFacadeMock,
        );
    }

    /**
     * @return array
     */
    public function getMerchantUserPositiveScenarioDataProvider(): array
    {
        return [
            'by id merchant user' => [
                'merchantUserCriteriaDataKeys' => [
                    MerchantUserCriteriaTransfer::ID_MERCHANT_USER,
                ],
                'isUserInCriteria' => false,
            ],
            'by id merchant' => [
                'merchantUserCriteriaDataKeys' => [
                    MerchantUserCriteriaTransfer::ID_MERCHANT,
                ],
                'isUserInCriteria' => false,
            ],
            'by id user' => [
                'merchantUserCriteriaDataKeys' => [
                    MerchantUserCriteriaTransfer::ID_USER,
                ],
                'isUserInCriteria' => false,
            ],
            'with user' => [
                'merchantUserCriteriaDataKeys' => [
                    MerchantUserCriteriaTransfer::ID_MERCHANT_USER,
                    MerchantUserCriteriaTransfer::ID_USER,
                    MerchantUserCriteriaTransfer::WITH_USER,
                ],
                'isUserInCriteria' => true,
            ],
        ];
    }

    /**
     * @return array
     */
    public function getMerchantUserNegativeScenarioDataProvider(): array
    {
        return [
            'by id merchant user' => [
                'merchantUserCriteriaData' => [
                    MerchantUserCriteriaTransfer::ID_MERCHANT_USER => 0,
                ],
            ],
            'by id merchant' => [
                'merchantUserCriteriaData' => [
                    MerchantUserCriteriaTransfer::ID_MERCHANT => 0,
                ],
            ],
            'by id user' => [
                'merchantUserCriteriaData' => [
                    MerchantUserCriteriaTransfer::ID_USER => 0,
                ],
            ],
        ];
    }
}
