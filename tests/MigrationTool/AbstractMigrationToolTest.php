<?php

namespace PetrKnap\Php\MigrationTool\Test;

use PetrKnap\Php\MigrationTool\AbstractMigrationTool;
use PetrKnap\Php\MigrationTool\Exception\MismatchException;
use PetrKnap\Php\MigrationTool\Test\AbstractMigrationToolTest\AbstractMigrationToolMock;
use PHPUnit_Framework_Error_Notice;
use PHPUnit_Framework_Error_Warning;
use Psr\Log\LoggerInterface;

class AbstractMigrationToolTest extends TestCase
{

    /**
     * @dataProvider dataMigrateMethodWorks
     * @param array $appliedMigrations
     * @param array $expectedAppliedMigrations
     * @param LoggerInterface $logger
     */
    public function testMigrateMethodWorks(array $appliedMigrations, array $expectedAppliedMigrations, LoggerInterface $logger = null)
    {
        $tool = new AbstractMigrationToolMock(
            $appliedMigrations,
            __DIR__ . "/AbstractMigrationToolTest/MigrateMethodWorks"
        );

        if ($logger) {
            $tool->setLogger($logger);
        }

        try {
            $tool->migrate();
        } catch (PHPUnit_Framework_Error_Notice $ignored) {
            // It throws notice if all migrations are applied
        }

        $this->assertEquals($expectedAppliedMigrations, $tool->getAppliedMigrations());
    }

    public function dataMigrateMethodWorks()
    {
        $expectedAppliedMigrations = array("2017-02-05.1", "2017-02-05.2", "2017-02-05.3");
        return array(
            array(array(), $expectedAppliedMigrations),
            array(array("2017-02-05.1"), $expectedAppliedMigrations),
            array(array("2017-02-05.1", "2017-02-05.2"), $expectedAppliedMigrations),
            array(array("2017-02-05.1", "2017-02-05.2", "2017-02-05.3"), $expectedAppliedMigrations),
        );
    }

    public function testMigrateMethodLogs()
    {
        $log = array();
        $data = $this->dataMigrateMethodWorks();
        $this->testMigrateMethodWorks(
            $data[0][0],
            $data[0][1],
            $this->getLogger($log)
        );
        $this->assertLogEquals(array(
            "info" => array(
                AbstractMigrationTool::MESSAGE__FOUND_MIGRATION_FILES__COUNT_PATH_PATTERN,
                AbstractMigrationTool::MESSAGE__MIGRATION_FILE_APPLIED__PATH,
                AbstractMigrationTool::MESSAGE__MIGRATION_FILE_APPLIED__PATH,
                AbstractMigrationTool::MESSAGE__MIGRATION_FILE_APPLIED__PATH,
                AbstractMigrationTool::MESSAGE__DONE,
            ),
        ), $log);
    }

    /**
     * @dataProvider dataThrowsMismatchExceptionIfThereIsGapeInMigrations
     * @param array $appliedMigrations
     * @param LoggerInterface $logger
     */
    public function testThrowsMismatchExceptionIfThereIsGapeInMigrations(array $appliedMigrations, LoggerInterface $logger = null)
    {
        $tool = new AbstractMigrationToolMock(
            $appliedMigrations,
            __DIR__ . "/AbstractMigrationToolTest/ThrowsMismatchExceptionIfThereIsGapeInMigrations"
        );

        if ($logger) {
            $tool->setLogger($logger);
        }

        try {
            $tool->migrate();
        } catch (MismatchException $exception) {
            $this->assertStringMatchesFormat(
                $this->getFormatForMessage(AbstractMigrationTool::MESSAGE__DETECTED_GAPE_BEFORE_MIGRATION__ID),
                $exception->getMessage()
            );
        }
    }

    public function dataThrowsMismatchExceptionIfThereIsGapeInMigrations()
    {
        return array(
            array(array("2017-02-05.2")),
            array(array("2017-02-05.3")),
            array(array("2017-02-05.1", "2017-02-05.3")),
            array(array("2017-02-05.2", "2017-02-05.3")),
        );
    }

    public function testLogsMismatchExceptionIfThereIsGapeInMigrations()
    {
        $log = array();
        $data = $this->dataThrowsMismatchExceptionIfThereIsGapeInMigrations();
        $this->testThrowsMismatchExceptionIfThereIsGapeInMigrations(
            $data[0][0],
            $this->getLogger($log)
        );
        $this->assertLogEquals(array(
            "info" => array(
                AbstractMigrationTool::MESSAGE__FOUND_MIGRATION_FILES__COUNT_PATH_PATTERN,
            ),
            "critical" => array(
                AbstractMigrationTool::MESSAGE__DETECTED_GAPE_BEFORE_MIGRATION__ID,
            ),
        ), $log);
    }

    public function testThrowsWarningIfMigrationFolderIsEmpty(LoggerInterface $logger = null)
    {
        $dir = __DIR__ . "/AbstractMigrationToolTest/ThrowsWarningIfMigrationFolderIsEmpty";
        @mkdir($dir);
        $tool = new AbstractMigrationToolMock(array(), $dir);

        if ($logger) {
            $tool->setLogger($logger);
        }

        try {
            $tool->migrate();
            $this->fail();
        } catch (PHPUnit_Framework_Error_Warning $warning) {
            $this->assertStringMatchesFormat(
                $this->getFormatForMessage(AbstractMigrationTool::MESSAGE__THERE_IS_NOTHING_TO_MIGRATE__PATH_PATTERN),
                $warning->getMessage()
            );
        }
    }

    public function testLogsWarningIfMigrationFolderIsEmpty()
    {
        $log = array();
        $this->testThrowsWarningIfMigrationFolderIsEmpty(
            $this->getLogger($log)
        );
        $this->assertLogEquals(array(
            "warning" => array(
                AbstractMigrationTool::MESSAGE__THERE_IS_NOTHING_TO_MIGRATE__PATH_PATTERN,
            ),
        ), $log);
    }

    public function testThrowsNoticeIfThereIsNothingToMigrate(LoggerInterface $logger = null)
    {
        $tool = new AbstractMigrationToolMock(
            array("2017-02-05.1", "2017-02-05.2", "2017-02-05.3"),
            __DIR__ . "/AbstractMigrationToolTest/ThrowsNoticeIfThereIsNothingToMigrate"
        );

        if ($logger) {
            $tool->setLogger($logger);
        }

        try {
            $tool->migrate();
            $this->fail();
        } catch (PHPUnit_Framework_Error_Notice $notice) {
            $this->assertStringMatchesFormat(
                $this->getFormatForMessage(AbstractMigrationTool::MESSAGE__THERE_IS_NOTHING_TO_MIGRATE__PATH_PATTERN),
                $notice->getMessage()
            );
        }
    }

    public function testLogsNoticeIfThereIsNothingToMigrate()
    {
        $log = array();
        $this->testThrowsNoticeIfThereIsNothingToMigrate(
            $this->getLogger($log)
        );
        $this->assertLogEquals(array(
            "info" => array(
                AbstractMigrationTool::MESSAGE__FOUND_MIGRATION_FILES__COUNT_PATH_PATTERN,
            ),
            "notice" => array(
                AbstractMigrationTool::MESSAGE__THERE_IS_NOTHING_TO_MIGRATE__PATH_PATTERN,
            ),
        ), $log);
    }

    public function testThrowsNoticeIfThereIsUnsupportedFile(LoggerInterface $logger = null)
    {
        $tool = new AbstractMigrationToolMock(
            array(),
            __DIR__ . "/AbstractMigrationToolTest/ThrowsNoticeIfThereIsUnsupportedFile"
        );

        if ($logger) {
            $tool->setLogger($logger);
        }

        try {
            $tool->migrate();
            $this->fail();
        } catch (PHPUnit_Framework_Error_Notice $notice) {
            $this->assertStringMatchesFormat(
                $this->getFormatForMessage(AbstractMigrationTool::MESSAGE__FOUND_UNSUPPORTED_FILE__PATH),
                $notice->getMessage()
            );
        }
    }

    public function testLogsNoticeIfThereIsUnsupportedFile()
    {
        $log = array();
        $this->testThrowsNoticeIfThereIsUnsupportedFile(
            $this->getLogger($log)
        );
        $this->assertLogEquals(array(
            "notice" => array(
                AbstractMigrationTool::MESSAGE__FOUND_UNSUPPORTED_FILE__PATH,
            ),
        ), $log);
    }

    // Old test below

    /**
     * @dataProvider dataMigrateWorks
     * @param array $appliedMigrations
     * @param \Exception $expectedException
     * @throws \Exception
     */
    public function testMigrateWorks($appliedMigrations, $expectedException = null)
    {
        $tool = new AbstractMigrationToolMock($appliedMigrations);

        if ($expectedException) {
            $this->setExpectedException(get_class($expectedException));
        }

        try {
            @$tool->migrate();
        } catch (\Exception $e) {
            $this->assertStringMatchesFormat($expectedException->getMessage(), $e->getMessage());
            throw $e;
        }
    }

    public function dataMigrateWorks()
    {
        return array(
            array(array(), null),
            array(array("2016-06-22.1"), null),
            array(array("2016-06-22.1", "2016-06-22.2"), null),
            array(array("2016-06-22.1", "2016-06-22.2", "2016-06-22.3"), null),
            array(array("2016-06-22.2"), new MismatchException("%a/2016-06-22.1 - First migration.ext")),
            array(array("2016-06-22.3"), new MismatchException("%a/2016-06-22.1 - First migration.ext%a/2016-06-22.2 - Second migration.ext")),
            array(array("2016-06-22.1", "2016-06-22.3"), new MismatchException("%a/2016-06-22.2 - Second migration.ext")),
            array(array("2016-06-22.2", "2016-06-22.3"), new MismatchException("%a/2016-06-22.1 - First migration.ext")),
        );
    }

    public function testGetMigrationFilesWorks()
    {
        $tool = new AbstractMigrationToolMock(array());

        $this->assertEquals(
            array(
                __DIR__ . "/AbstractMigrationToolTest/migrations/2016-06-22.1 - First migration.ext",
                __DIR__ . "/AbstractMigrationToolTest/migrations/2016-06-22.2 - Second migration.ext",
                __DIR__ . "/AbstractMigrationToolTest/migrations/2016-06-22.3 - Third migration.ext",
            ),
            @$this->invokeMethods($tool, array(array("getMigrationFiles")))
        );

        $this->setExpectedException("PHPUnit_Framework_Error_Notice");
        $this->invokeMethods($tool, array(array("getMigrationFiles")));
    }

    /**
     * @dataProvider dataGetMigrationIdWorks
     * @param string $pathToMigrationFile
     * @param string $expectedMigrationId
     */
    public function testGetMigrationIdWorks($pathToMigrationFile, $expectedMigrationId)
    {
        $tool = new AbstractMigrationToolMock(array());

        $this->assertEquals(
            $expectedMigrationId,
            $this->invokeMethods($tool, array(array("getMigrationId", array($pathToMigrationFile))))
        );
    }

    public function dataGetMigrationIdWorks()
    {
        return array(
            array("/migration_file.ext", "migration_file"),
            array("/migration file.ext", "migration"),
            array("/migration_file", "migration_file"),
            array("/migration file", "migration")
        );
    }

    /**
     * @dataProvider dataLoggingWorks
     * @param array $appliedMigrations
     * @param array $expectedLog
     */
    public function testLoggingWorks(array $appliedMigrations, array $expectedLog)
    {
        $log = array();

        try {
            /** @var LoggerInterface $logger */
            $tool = new AbstractMigrationToolMock($appliedMigrations);
            $tool->setLogger($this->getLogger($log));
            @$tool->migrate();
        } catch (\Exception $ignored) {
            // Ignored exception
        }

        $this->assertLogEquals($expectedLog, $log);
    }

    public function dataLoggingWorks()
    {
        return array(
            array(array(), array(
                "notice" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_UNSUPPORTED_FILE__PATH,
                ),
                "info" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_MIGRATION_FILES__COUNT_PATH_PATTERN,
                    AbstractMigrationTool::MESSAGE__MIGRATION_FILE_APPLIED__PATH,
                    AbstractMigrationTool::MESSAGE__MIGRATION_FILE_APPLIED__PATH,
                    AbstractMigrationTool::MESSAGE__MIGRATION_FILE_APPLIED__PATH,
                    AbstractMigrationTool::MESSAGE__DONE,
                ),
            )),
            array(array("2016-06-22.1"), array(
                "notice" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_UNSUPPORTED_FILE__PATH,
                ),
                "info" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_MIGRATION_FILES__COUNT_PATH_PATTERN,
                    AbstractMigrationTool::MESSAGE__MIGRATION_FILE_APPLIED__PATH,
                    AbstractMigrationTool::MESSAGE__MIGRATION_FILE_APPLIED__PATH,
                    AbstractMigrationTool::MESSAGE__DONE,
                ),
            )),
            array(array("2016-06-22.1", "2016-06-22.2"), array(
                "notice" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_UNSUPPORTED_FILE__PATH,
                ),
                "info" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_MIGRATION_FILES__COUNT_PATH_PATTERN,
                    AbstractMigrationTool::MESSAGE__MIGRATION_FILE_APPLIED__PATH,
                    AbstractMigrationTool::MESSAGE__DONE,
                ),
            )),
            array(array("2016-06-22.1", "2016-06-22.2", "2016-06-22.3"), array(
                "notice" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_UNSUPPORTED_FILE__PATH,
                    AbstractMigrationTool::MESSAGE__THERE_IS_NOTHING_TO_MIGRATE__PATH_PATTERN,
                ),
                "info" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_MIGRATION_FILES__COUNT_PATH_PATTERN,
                    AbstractMigrationTool::MESSAGE__DONE,
                ),
            )),
            array(array("2016-06-22.2"), array(
                "notice" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_UNSUPPORTED_FILE__PATH,
                ),
                "info" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_MIGRATION_FILES__COUNT_PATH_PATTERN,
                ),
                "critical" => array(
                    AbstractMigrationTool::MESSAGE__DETECTED_GAPE_BEFORE_MIGRATION__ID,
                )
            )),
            array(array("2016-06-22.3"), array(
                "notice" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_UNSUPPORTED_FILE__PATH,
                ),
                "info" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_MIGRATION_FILES__COUNT_PATH_PATTERN,
                ),
                "critical" => array(
                    AbstractMigrationTool::MESSAGE__DETECTED_GAPE_BEFORE_MIGRATION__ID,
                ),
            )),
            array(array("2016-06-22.1", "2016-06-22.3"), array(
                "notice" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_UNSUPPORTED_FILE__PATH,
                ),
                "info" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_MIGRATION_FILES__COUNT_PATH_PATTERN,
                ),
                "critical" => array(
                    AbstractMigrationTool::MESSAGE__DETECTED_GAPE_BEFORE_MIGRATION__ID,
                ),
            )),
            array(array("2016-06-22.2", "2016-06-22.3"), array(
                "notice" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_UNSUPPORTED_FILE__PATH,
                ),
                "info" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_MIGRATION_FILES__COUNT_PATH_PATTERN,
                ),
                "critical" => array(
                    AbstractMigrationTool::MESSAGE__DETECTED_GAPE_BEFORE_MIGRATION__ID,
                ),
            )),
        );
    }
}
