<?php

namespace Scaleplan\Youtube;

use Psr\Http\Message\RequestInterface;
use Scaleplan\Youtube\Exceptions\YoutubeException;
use Scaleplan\Youtube\Structures\VideoDataStructure;

/**
 * Class Video
 *
 * @package Scaleplan\Youtube
 */
class Video extends AbstractApi
{
    /**
     * Upload the video to YouTube
     *
     * @param string $path
     * @param VideoDataStructure $data
     *
     * @return \Google_Service_YouTube_Video
     *
     * @throws YoutubeException
     */
    public function upload(string $path, VideoDataStructure $data) : \Google_Service_YouTube_Video
    {
        if (!file_exists($path)) {
            throw new YoutubeException(
                "Video file does not exist at path: $path. Provide a full path to the file before attempting to upload."
            );
        }

        $fileSize = filesize($path);
        $video = $this->getVideo($data);
        // Set the Chunk Size
        $chunkSize = 1 * 1024 * 1024;
        if ($fileSize > $chunkSize) {
            // Set the defer to true
            $this->client->setDefer(true);

            $optParams = [
                'autoLevels' => true,
                'stabilize'  => true,
            ];
            // Build the request
            /** @var RequestInterface $request */
            $request = $this->youtube->videos->insert('status,snippet', $video, $optParams);
            // Upload
            $media = new \Google_Http_MediaFileUpload(
                $this->client,
                $request,
                'video/*',
                null,
                true,
                $chunkSize
            );
            // Set the File size
            $media->setFileSize($fileSize);
            // Read the file and upload in chunks
            $status = false;
            $handle = fopen($path, 'rb');
            while (!$status && !feof($handle)) {
                $chunk = fread($handle, $chunkSize);
                $status = $media->nextChunk($chunk);
            }

            fclose($handle);
            $video->setId($status['id']);
        } else {
            $this->client->setDefer(false);
            $video = $this->youtube->videos->insert(
                'status,snippet',
                $video,
                [
                    'data'       => file_get_contents($path),
                    'mimeType'   => 'video/*',
                    'uploadType' => 'media',
                ]
            );
        }

        $this->client->setDefer(false);

        return $video;
    }

    /**
     * Update YouTube video
     *
     * @param VideoDataStructure $data
     *
     * @return \Google_Service_YouTube_Video
     *
     * @throws YoutubeException
     */
    public function update(
        VideoDataStructure $data
    ) : \Google_Service_YouTube_Video
    {
        if (!$this->exists($data->getId())) {
            throw new YoutubeException("A video matching id {$data->getId()} could not be found.");
        }

        $this->client->setDefer(false);
        $video = $this->getVideo($data);
        return $this->youtube->videos->update('status,snippet', $video);
    }

    /**
     * Set a Custom Thumbnail for a video
     *
     * @param string $imagePath
     * @param string $id
     *
     * @return \Google_Service_YouTube_ThumbnailSetResponse
     *
     * @throws YoutubeException
     */
    public function setThumbnail(string $imagePath, string $id) : \Google_Service_YouTube_ThumbnailSetResponse
    {
        if (!$this->exists($id)) {
            throw new YoutubeException('A video matching id "' . $id . '" could not be found.');
        }

        return $this->youtube->thumbnails->set(
            $id,
            [
                'data'       => file_get_contents($imagePath),
                'mimeType'   => 'image/*',
                'uploadType' => 'media',
            ]
        );
    }

    /**
     * Delete a YouTube video by it's ID.
     *
     * @param string $id
     *
     * @throws YoutubeException
     */
    public function delete(string $id) : void
    {
        if (!$this->exists($id)) {
            throw new YoutubeException('A video matching id "' . $id . '" could not be found.');
        }

        $this->youtube->videos->delete($id);
    }


    /**
     * Create video object from array data
     *
     * @param VideoDataStructure $data
     *
     * @return \Google_Service_YouTube_Video
     */
    private function getVideo(VideoDataStructure $data) : \Google_Service_YouTube_Video
    {
        // Setup the Snippet
        $snippet = new \Google_Service_YouTube_VideoSnippet();

        if ($data->getTitle()) {
            $snippet->setTitle($data->getTitle());
        }
        if ($data->getDescription()) {
            $snippet->setDescription($data->getDescription());
        }
        if ($data->getTags()) {
            $snippet->setTags($data->getTags());
        }
        if ($data->getCategoryId()) {
            $snippet->setCategoryId($data->getCategoryId());
        }
        if ($data->getChannelId()) {
            $snippet->setChannelId($data->getChannelId());
        }
        if ($data->getDefaultLanguage()) {
            $snippet->setDefaultLanguage($data->getDefaultLanguage());
        }

        // Set the Privacy Status
        $status = new \Google_Service_YouTube_VideoStatus();
        $status->privacyStatus = $data->getPrivacyStatus();

        // Set the Snippet & Status
        $video = new \Google_Service_YouTube_Video();
        if ($data->getId()) {
            $video->setId($data->getId());
        }

        $video->setSnippet($snippet);
        $video->setStatus($status);

        return $video;
    }

    /**
     * Check if a YouTube video exists by it's ID.
     *
     * @param int $id
     *
     * @return bool
     */
    public function exists($id) : bool
    {
        $response = $this->youtube->videos->listVideos('status', ['id' => $id]);

        if (empty($response->items)) {
            return false;
        }

        return true;
    }
}
