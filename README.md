# PHP SDK for Zitadel

This is the Zitadel PHP SDK, designed to provide a convenient and idiomatic
way to interact with the Zitadel APIs in PHP. The SDK provides a seamless
wrapping of the Zitadel API, making it easy to authenticate service users and
perform API operations.

The SDK enables efficient integration with the Zitadel API, allowing you to
manage resources and execute actions. However, it's important to note that
this SDK is tailored for service users and is not intended for user
authentication scenarios. It does not support authentication mechanisms
like OAuth2, OIDC, or SAML for client applications, including web, mobile,
or other environments. For these types of user authentication, you should
use other libraries that are designed for the specific platform and
authentication method.

**Disclaimer**: This SDK is not suitable for implementing user authentication.
It does not handle authentication for client applications using OAuth2, OIDC,
or SAML and should not be used for scenarios requiring such functionality.
For those use cases, consider using other solutions that are designed for
user authentication across various platforms like web, mobile, or other
client environments.

> [!IMPORTANT]
> Please be aware that this SDK is currently in an incubating stage. We are releasing it to the community to gather
> feedback and learn how it is being used. While you are welcome to use it, please note that the API and functionality may
> evolve based on community input. We encourage you to try it out and share your experiences, but advise caution when
> considering it for production environments as future updates may introduce changes.

## Getting Started

### Sign up for Zitadel

To use this SDK, you need a Zitadel account. Sign up at the official
Zitadel website and obtain the necessary credentials to access the API.

### Minimum Requirements

Ensure you have PHP 8.0 or higher installed. You also need Composer to
install dependencies.

## Using the SDK

### Installation

Install the SDK by running one of the following commands:

```bash
composer require zitadel/client
```

## Authentication Methods

Your SDK offers three ways to authenticate with Zitadel. Each method has its
own benefits—choose the one that fits your situation best.

#### 1. Private Key JWT Authentication

**What is it?**
You use a JSON Web Token (JWT) that you sign with a private key stored in a
JSON file. This process creates a secure token.

**When should you use it?**

- **Best for production:** It offers strong security.
- **Advanced control:** You can adjust token settings like expiration.

**How do you use it?**

1. Save your private key in a JSON file.
2. Use the provided method to load this key and create a JWT-based
   authenticator.

**Example:**

```php
use \Zitadel\Client\Zitadel;

$zitadel = Zitadel::withPrivateKey("https://example.us1.zitadel.cloud", "path/to/jwt-key.json");

try {
    $response = $zitadel->users->addHumanUser((new UserServiceAddHumanUserRequest())
        ->setUsername('john.doe')
        ->setProfile(
            (new UserServiceSetHumanProfile())
                ->setGivenName('John')
                ->setFamilyName('Doe')
        )
        ->setEmail(
            (new UserServiceSetHumanEmail())
                ->setEmail('john@doe.com')
        ));
    echo "User created: " . print_r($response, true);
} catch (ApiException $e) {
    echo "Error: " . $e->getMessage();
```

#### 2. Client Credentials Grant

**What is it?**
This method uses a client ID and client secret to get a secure access token,
which is then used to authenticate.

**When should you use it?**

- **Simple and straightforward:** Good for server-to-server communication.
- **Trusted environments:** Use it when both servers are owned or trusted.

**How do you use it?**

1. Provide your client ID and client secret.
2. Build the authenticator

**Example:**

```php
use Zitadel\Client\Zitadel;
use Zitadel\Client\Model\UserServiceAddHumanUserRequest;
use \Zitadel\Client\Model\UserServiceAddHumanUserRequest;
use \Zitadel\Client\Model\UserServiceSetHumanProfile;
use \Zitadel\Client\Model\UserServiceSetHumanEmail;

$zitadel = Zitadel::withClientCredentials("https://example.us1.zitadel.cloud", "id", "secret");

try {
    $response = $zitadel->users->addHumanUser((new UserServiceAddHumanUserRequest())
        ->setUsername('john.doe')
        ->setProfile(
            (new UserServiceSetHumanProfile())
                ->setGivenName('John')
                ->setFamilyName('Doe')
        )
        ->setEmail(
            (new UserServiceSetHumanEmail())
                ->setEmail('john@doe.com')
        ));
    echo "User created: " . print_r($response, true);
} catch (ApiException $e) {
    echo "Error: " . $e->getMessage();
}
```

#### 3. Personal Access Tokens (PATs)

**What is it?**
A Personal Access Token (PAT) is a pre-generated token that you can use to
authenticate without exchanging credentials every time.

**When should you use it?**

- **Easy to use:** Great for development or testing scenarios.
- **Quick setup:** No need for dynamic token generation.

**How do you use it?**

1. Obtain a valid personal access token from your account.
2. Create the authenticator with: `PersonalAccessTokenAuthenticator`

**Example:**

```php
use \Zitadel\Client\Zitadel;
use Zitadel\Client\Zitadel;
use Zitadel\Client\Model\UserServiceAddHumanUserRequest;
use \Zitadel\Client\Model\UserServiceAddHumanUserRequest;
use \Zitadel\Client\Model\UserServiceSetHumanProfile;
use \Zitadel\Client\Model\UserServiceSetHumanEmail;

$zitadel = Zitadel::withAccessToken("https://example.us1.zitadel.cloud", "token");

try {
    $response = $zitadel->users->addHumanUser(
        (new UserServiceAddHumanUserRequest())
            ->setUsername('john.doe')
            ->setProfile(
                (new UserServiceSetHumanProfile())
                    ->setGivenName('John')
                    ->setFamilyName('Doe')
            )
            ->setEmail(
                (new UserServiceSetHumanEmail())
                    ->setEmail('john@doe.com')
            )
    );
    echo "User created: " . print_r($response, true);
} catch (ApiException $e) {
    echo "Error: " . $e->getMessage();
}
```

---

Choose the authentication method that best suits your needs based on your
environment and security requirements. For more details, please refer to the
[Zitadel documentation on authenticating service users](https://zitadel.com/docs/guides/integrate/service-users/authenticate-service-users).

## Design and Dependencies

This SDK is designed to be lean and efficient, focusing on providing a
streamlined way to interact with the Zitadel API. It relies on Guzzle's
PSR-7 compliant HTTP transport for making requests, which ensures that
the SDK integrates well with other libraries and provides flexibility
in terms of request handling and error management.

## Versioning

A key aspect of our strategy is that the SDK's major version is synchronized with the ZITADEL core project's major
version to ensure compatibility. For a detailed explanation of this policy and our release schedule, please see
our [Versioning Guide](VERSIONING.md).

## Contributing

This repository is autogenerated. We do not accept direct contributions.
Instead, please open an issue for any bugs or feature requests.

## Reporting Issues

If you encounter any issues or have suggestions for improvements, please
open an issue in the [issue tracker](https://github.com/zitadel/client-php/issues).
When reporting an issue, please provide the following information to help
us address it more effectively:

- A detailed description of the problem or feature request
- Steps to reproduce the issue (if applicable)
- Any relevant error messages or logs
- Environment details (e.g., OS version, relevant configurations)

## Support

If you need help setting up or configuring the SDK (or anything
Zitadel), please head over to the [Zitadel Community on Discord](https://zitadel.com/chat).

There are many helpful people in our Discord community who are ready to
assist you.

Cloud and enterprise customers can additionally reach us privately via our
[support communication channels](https://zitadel.com/docs/legal/service-description/support-services).

## License

This SDK is distributed under the Apache 2.0 License.
