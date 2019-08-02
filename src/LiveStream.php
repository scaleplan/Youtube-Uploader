<?php

namespace Scaleplan\Youtube;

use Scaleplan\Youtube\Constants\FrameRates;
use Scaleplan\Youtube\Constants\IngestionTypes;
use Scaleplan\Youtube\Constants\LatencyPreferences;
use Scaleplan\Youtube\Constants\Resolutions;
use Scaleplan\Youtube\Constants\PrivacyStatuses;

/**
 * Class LiveStream
 *
 * @package Scaleplan\Youtube
 */
class LiveStream extends AbstractApi
{
    /**
     * Creating broadcast
     *
     * @param string $title
     * @param \DateTimeInterface $startTime
     * @param \DateTimeInterface $endTime
     * @param bool $enableDvr
     * @param bool $recording
     * @param string $privacyStatus
     *
     * @return \Google_Service_YouTube_LiveBroadcast
     */
    public function createBroadcast(
        string $title,
        \DateTimeInterface $startTime,
        \DateTimeInterface $endTime,
        bool $enableDvr = true,
        bool $recording = false,
        string $privacyStatus = PrivacyStatuses::PUBLIC
    ) : \Google_Service_YouTube_LiveBroadcast
    {
        $liveBroadcastResource = new \Google_Service_YouTube_LiveBroadcast();

        $contentDetails = new \Google_Service_YouTube_LiveBroadcastContentDetails();
        $contentDetails->setEnableDvr($enableDvr);
        $contentDetails->setEnableContentEncryption(true);
        $contentDetails->setEnableLowLatency(true);
        $contentDetails->setLatencyPreference(LatencyPreferences::NORMAL);
        $contentDetails->setEnableAutoStart(false);
        $contentDetails->setRecordFromStart($recording);
        $liveBroadcastResource->setContentDetails($contentDetails);

        $snippet = new \Google_Service_YouTube_LiveBroadcastSnippet();
        $snippet->setTitle($title);
        $snippet->setScheduledStartTime($startTime->format('c'));
        $snippet->setScheduledEndTime($endTime->format('c'));
        $liveBroadcastResource->setSnippet($snippet);

        $status = new \Google_Service_YouTube_LiveBroadcastStatus();
        $status->setPrivacyStatus($privacyStatus);
        $liveBroadcastResource->setStatus($status);

        return $this->youtube->liveBroadcasts->insert('snippet,status', $liveBroadcastResource);
    }

    /**
     * Create live stream
     *
     * @param string $title
     * @param bool $isReusable
     * @param string $ingestionType
     * @param string $resolution
     * @param string $frameRate
     * @param string|null $channelId
     *
     * @return \Google_Service_YouTube_LiveStream
     */
    public function createLiveStream(
        string $title,
        bool $isReusable,
        string $ingestionType = IngestionTypes::HLS,
        string $resolution = Resolutions::FORMAT_1080P,
        string $frameRate = FrameRates::RATE_30FPS,
        string $channelId = null
    ) : \Google_Service_YouTube_LiveStream
    {
        $liveStreamResource = new \Google_Service_YouTube_LiveStream();

        $contentDetails = new \Google_Service_YouTube_LiveStreamContentDetails();
        $contentDetails->setIsReusable($isReusable);
        $liveStreamResource->setContentDetails($contentDetails);

        $snippet = new \Google_Service_YouTube_LiveStreamSnippet();
        $snippet->setTitle($title);
        if (null !== $channelId) {
            $snippet->setChannelId($channelId);
        }
        $liveStreamResource->setSnippet($snippet);

        $cdn = new \Google_Service_YouTube_CdnSettings();
        $cdn->setIngestionType($ingestionType);
        $cdn->setResolution($resolution);
        $cdn->setFrameRate($frameRate);
        $liveStreamResource->setCdn($cdn);

        return $this->youtube->liveStreams->insert('snippet,status,cdn', $liveStreamResource);
    }
}
