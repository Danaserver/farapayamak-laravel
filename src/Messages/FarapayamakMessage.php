<?php

namespace Farapayamak\Laravel\Messages;

class FarapayamakMessage
{
    protected ?string $content = null;
    protected int|string|null $patternId = null;
    protected array $variables = [];
    protected ?string $from = null;
    protected ?string $to = null;
    protected bool $isFlash = false;
    protected ?string $speechText = null;

    public function __construct(?string $content = null)
    {
        $this->content = $content;
    }

    public static function create(?string $content = null): self
    {
        return new self($content);
    }

    /**
     * Set SMS text content.
     */
    public function content(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Set service pattern / template (BodyId or alias defined in config).
     */
    public function pattern(int|string $patternId, array $variables = []): self
    {
        $this->patternId = $patternId;
        if (!empty($variables)) {
            $this->variables = $variables;
        }
        return $this;
    }

    /**
     * Set variables to substitute in the service pattern.
     */
    public function variables(array $variables): self
    {
        $this->variables = $variables;
        return $this;
    }

    /**
     * Alias for variables.
     */
    public function vars(array $variables): self
    {
        return $this->variables($variables);
    }

    /**
     * Set custom sender number.
     */
    public function from(string $from): self
    {
        $this->from = $from;
        return $this;
    }

    /**
     * Override recipient phone number.
     */
    public function to(string $to): self
    {
        $this->to = $to;
        return $this;
    }

    /**
     * Mark as flash SMS.
     */
    public function flash(bool $isFlash = true): self
    {
        $this->isFlash = $isFlash;
        return $this;
    }

    /**
     * Add Text-To-Speech voice text.
     */
    public function voice(string $speechText): self
    {
        $this->speechText = $speechText;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getPatternId(): int|string|null
    {
        return $this->patternId;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function getFrom(): ?string
    {
        return $this->from;
    }

    public function getTo(): ?string
    {
        return $this->to;
    }

    public function isFlash(): bool
    {
        return $this->isFlash;
    }

    public function getSpeechText(): ?string
    {
        return $this->speechText;
    }

    public function isPattern(): bool
    {
        return !empty($this->patternId);
    }
}
