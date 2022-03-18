<?php

declare(strict_types=1);

/*
 * @copyright 2021 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2021 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\Mail\Tests\Unit\BackgroundJob;

use ChristophWurst\Nextcloud\Testing\ServiceMockObject;
use ChristophWurst\Nextcloud\Testing\TestCase;
use OC\BackgroundJob\JobList;
use OCA\Mail\Account;
use OCA\Mail\BackgroundJob\SyncJob;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\ILogger;
use OCP\IUser;
use function fwrite;

class SyncJobTest extends TestCase {

	/** @var ServiceMockObject*/
	private $serviceMock;

	/** @var SyncJob */
	private $job;

	protected function setUp(): void {
		fwrite(STDERR, "1\n");
		parent::setUp();
		fwrite(STDERR, "2\n");

		$this->serviceMock = $this->createServiceMock(SyncJob::class);
		fwrite(STDERR, "10\n");
		$this->job = $this->serviceMock->getService();
		fwrite(STDERR, "11\n");

		// Make sure the job is actually run
		$this->serviceMock->getParameter('time')
			->method('getTime')
			->willReturn(500000);
		fwrite(STDERR, "12\n");
		// Set our common argument
		$this->job->setArgument([
			'accountId' => 123,
		]);
		fwrite(STDERR, "13\n");
		// Set a fake ID
		$this->job->setId(99);
		fwrite(STDERR, "14\n");
	}

	public function testAccountDoesntExist(): void {
		fwrite(STDERR, "TADE 1\n");
		$this->serviceMock->getParameter('accountService')
			->expects(self::once())
			->method('findById')
			->with(123)
			->willThrowException(new DoesNotExistException(''));
		fwrite(STDERR, "TADE 2\n");
		$this->serviceMock->getParameter('logger')
			->expects(self::once())
			->method('debug')
			->with('Could not find account <123> removing from jobs');
		fwrite(STDERR, "TADE 3\n");
		$this->serviceMock->getParameter('jobList')
			->expects(self::once())
			->method('remove')
			->with(SyncJob::class, ['accountId' => 123]);
		fwrite(STDERR, "TADE 4\n");
		$this->serviceMock->getParameter('mailboxSync')
			->expects(self::never())
			->method('sync');
		fwrite(STDERR, "TADE 5\n");
		$this->serviceMock->getParameter('syncService')
			->expects(self::never())
			->method('syncAccount');
		fwrite(STDERR, "TADE 6\n");

		$this->job->setArgument([
			'accountId' => 123,
		]);
		fwrite(STDERR, "TADE 7\n");
		$this->job->setLastRun(0);
		fwrite(STDERR, "TADE 8\n");
		$this->job->execute(
			$this->createMock(JobList::class),
			$this->createMock(ILogger::class)
		);
		fwrite(STDERR, "TADE 9\n");
	}

	public function testUserDoesntExist(): void {
		$account = $this->createMock(Account::class);
		$account->method('getId')->willReturn(123);
		$account->method('getUserId')->willReturn('user123');
		$this->serviceMock->getParameter('accountService')
			->expects(self::once())
			->method('findById')
			->with(123)
			->willReturn($account);
		$user = $this->createMock(IUser::class);
		$this->serviceMock->getParameter('userManager')
			->expects(self::once())
			->method('get')
			->with('user123')
			->willReturn($user);
		$this->serviceMock->getParameter('logger')
			->expects(self::once())
			->method('debug')
			->with('Account 123 of user user123 could not be found or was disabled, skipping background sync');
		$this->serviceMock->getParameter('mailboxSync')
			->expects(self::never())
			->method('sync');
		$this->serviceMock->getParameter('syncService')
			->expects(self::never())
			->method('syncAccount');

		$this->job->setArgument([
			'accountId' => 123,
		]);
		$this->job->execute(
			$this->createMock(JobList::class),
			$this->createMock(ILogger::class)
		);
	}
}
