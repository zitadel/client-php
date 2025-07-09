<?php

namespace Zitadel\Client\Spec;

use Exception;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Abstract base class for integration tests that interact with a Docker
 * Compose stack.
 *
 * This class handles the lifecycle of a Docker Compose environment,
 * bringing it up before tests run and tearing it down afterwards. It also
 * provides mechanisms to load specific data (like authentication tokens
 * and JWT keys) from files and make them accessible via protected getters
 * for use in concrete test implementations.
 */
abstract class AbstractIntegrationTest extends TestCase
{
    /**
     * @var string|null The authentication token loaded from file.
     */
    protected static ?string $authToken = null;
    /**
     * @var string|null The absolute path to the JWT key file.
     */
    protected static ?string $jwtKey = null;
    /**
     * @var string|null The base URL for the services.
     */
    protected static ?string $baseUrl = null;
    /**
     * @var string The absolute path to the docker-compose.yaml file.
     */
    private static string $composeFilePath = __DIR__ . '/../etc/docker-compose.yaml';
    /**
     * @var string|null The directory containing the docker-compose.yaml file.
     */
    private static ?string $composeFileDir = null;

    /**
     * Sets up the test environment before the first test in the class runs.
     * This includes bringing up the Docker Compose stack and exposing
     * necessary data.
     *
     * @throws RuntimeException If the Docker Compose stack fails to start.
     * @throws Exception If a required file for data is not found or
     * cannot be read.
     */
    public static function setUpBeforeClass(): void
    {
        self::$composeFileDir = dirname(self::$composeFilePath);

        echo "Bringing up Docker Compose stack...\n";
        $command = "docker compose -f " . escapeshellarg(self::$composeFilePath)
            . " up --detach --no-color --quiet-pull --yes";
        exec($command, $output, $returnCode);

        foreach ($output as $line) {
            echo "STDOUT: " . $line . "\n";
        }

        if ($returnCode !== 0) {
            $errorMessage = "Failed to bring up Docker Compose stack. Exit code: "
                . $returnCode . "\n" . implode("\n", $output);
            error_log($errorMessage);
            throw new RuntimeException($errorMessage);
        }
        echo "Docker Compose stack is up.\n";

        // Load AUTH_TOKEN content from file
        self::loadFileContentIntoProperty('zitadel_output/pat.txt', 'authToken');

        // Set JWT_KEY to the absolute path of the file
        $jwtKeyFilePath = self::$composeFileDir . DIRECTORY_SEPARATOR
            . 'zitadel_output/sa-key.json';
        if (!file_exists($jwtKeyFilePath)) {
            throw new Exception("JWT Key file not found at path: $jwtKeyFilePath");
        }
        self::$jwtKey = $jwtKeyFilePath;
        echo "Loaded JWT_KEY path: " . self::$jwtKey . "\n";


        self::$baseUrl = 'http://localhost:8099';
        echo "Exposed BASE_URL as: " . self::$baseUrl . "\n";

        sleep(20);
    }

    /**
     * Reads the content of a file relative to the compose file directory and
     * assigns it to a specified static property of this class.
     * This method is intended for loading *content*, not paths.
     *
     * @param string $relativePath The path to the file, relative to the
     * compose file's directory.
     * @param string $propertyName The name of the static property (e.g.,
     * 'authToken') to assign the content to.
     * @throws Exception If the file is not found or cannot be read.
     * @noinspection PhpSameParameterValueInspection
     */
    private static function loadFileContentIntoProperty(
        string $relativePath,
        string $propertyName
    ): void {
        $filePath = self::$composeFileDir . DIRECTORY_SEPARATOR . $relativePath;

        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            if ($content !== false) {
                self::$$propertyName = trim($content);
                echo "Loaded $filePath content into property: $propertyName\n";
            } else {
                throw new Exception("Could not read content of file: $filePath for "
                    . "property '$propertyName'");
            }
        } else {
            throw new Exception("File not found for property '$propertyName': "
                . "$filePath");
        }
    }

    /**
     * Tears down the test environment after all tests in the class have run.
     * This includes stopping and removing the Docker Compose stack.
     *
     * @throws Exception If the Docker Compose file path is invalid or the
     * stack fails to tear down.
     */
    public static function tearDownAfterClass(): void
    {
        echo "Tearing down Docker Compose stack...\n";
        if (file_exists(self::$composeFilePath)) {
            $command = "docker compose -f " . escapeshellarg(self::$composeFilePath)
                . " down -v";
            exec($command, $output, $returnCode);

            foreach ($output as $line) {
                echo "STDOUT: " . $line . "\n";
            }

            if ($returnCode !== 0) {
                error_log("Warning: Failed to tear down Docker Compose stack. Exit "
                    . "code: " . $returnCode . "\n" . implode("\n", $output));
                throw new Exception(
                    "Failed to tear down Docker Compose stack. Exit code: "
                    . $returnCode . "\n" . implode("\n", $output)
                );
            }
        } else {
            throw new Exception("Docker Compose file path not initialized or file "
                . "does not exist, skipping tear down.");
        }
        echo "Docker Compose stack torn down.\n";
    }

    /**
     * Retrieves the authentication token.
     *
     * @return string|null The authentication token, or null if not set.
     */
    protected static function getAuthToken(): ?string
    {
        return self::$authToken;
    }

    /**
     * Retrieves the absolute path to the JWT key file.
     *
     * @return string|null The absolute path to the JWT key file, or null
     * if not set.
     */
    protected static function getJwtKey(): ?string
    {
        return self::$jwtKey;
    }

    /**
     * Retrieves the base URL.
     *
     * @return string|null The base URL, or null if not set.
     */
    protected static function getBaseUrl(): ?string
    {
        return self::$baseUrl;
    }
}
