<?php

namespace Scaleplan\Youtube\Structures;

use Scaleplan\Youtube\Constants\PrivacyStatuses;

/**
 * Class VideoDataStructure
 *
 * @package Scaleplan\Youtube\Structures
 */
final class VideoDataStructure
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var array
     */
    private $tags = [];

    /**
     * @var string
     */
    private $categoryId;

    /**
     * @var string
     */
    private $channelId;

    /**
     * @var string
     */
    private $defaultLanguage;

    /**
     * @var string
     */
    private $privacyStatus = PrivacyStatuses::PUBLIC;

    /**
     * @var string
     */
    private $id;

    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title) : void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description) : void
    {
        $this->description = $description;
    }

    /**
     * @return array
     */
    public function getTags() : array
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     */
    public function setTags(array $tags) : void
    {
        $this->tags = $tags;
    }

    /**
     * @return string
     */
    public function getCategoryId() : string
    {
        return $this->categoryId;
    }

    /**
     * @param string $categoryId
     */
    public function setCategoryId(string $categoryId) : void
    {
        $this->categoryId = $categoryId;
    }

    /**
     * @return string
     */
    public function getChannelId() : string
    {
        return $this->channelId;
    }

    /**
     * @param string $channelId
     */
    public function setChannelId(string $channelId) : void
    {
        $this->channelId = $channelId;
    }

    /**
     * @return string
     */
    public function getDefaultLanguage() : string
    {
        return $this->defaultLanguage;
    }

    /**
     * @param string $defaultLanguage
     */
    public function setDefaultLanguage(string $defaultLanguage) : void
    {
        $this->defaultLanguage = $defaultLanguage;
    }

    /**
     * @return string
     */
    public function getPrivacyStatus() : string
    {
        return $this->privacyStatus;
    }

    /**
     * @param string $privacyStatus
     */
    public function setPrivacyStatus(string $privacyStatus) : void
    {
        $this->privacyStatus = $privacyStatus;
    }

    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id) : void
    {
        $this->id = $id;
    }
}
