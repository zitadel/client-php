<?php

declare(strict_types=1);

namespace Zitadel\Client\Test;

use Symfony\Component\Serializer\Attribute\SerializedName;

/**
 * A stand-in model part for the multipart serialization test below. It is
 * declared here rather than taken from the generated models so the test holds
 * for EVERY spec this SDK is generated from — no spec is guaranteed to contain
 * a model with these properties. It is shaped exactly like a generated model:
 * the PHP property names ARE the wire names, which is what the SDK's
 * ObjectSerializer emits (its normalizer is built without a name converter).
 */
final class MultipartModelPart
{
    #[SerializedName('isEnabled')]
    public ?bool $isEnabled = null;

    #[SerializedName('recordedAt')]
    public ?\DateTime $recordedAt = null;

    public function __construct(?bool $isEnabled = null, ?\DateTime $recordedAt = null)
    {
        $this->isEnabled = $isEnabled;
        $this->recordedAt = $recordedAt;
    }
}
