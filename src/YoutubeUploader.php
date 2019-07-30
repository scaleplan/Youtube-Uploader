<?php

namespace Scaleplan\Youtube;

use Psr\Http\Message\RequestInterface;
use Scaleplan\Youtube\Constants\PrivacyStatuses;
use Scaleplan\Youtube\Exceptions\YoutubeException;

/**
 * Class Youtube
 *
 * @package Scaleplan\Youtube
 */
class Youtube
{
    /**
     * Google Client
     *
     * @var \Google_Client
     */
    protected $client;

    /**
     * Google YouTube Service
     *
     * @var \Google_Service_YouTube
     */
    protected $youtube;

    /**
     * Constructor
     *
     * @param \Google_Client $client
     */
    public function __construct(\Google_Client $client = null)
    {
        $this->client = $client ?? new \Google_Client;
        $this->client->setAccessType('offline');
        $this->client->useApplicationDefaultCredentials();
        $this->client->addScope(\Google_Service_YouTube::YOUTUBE);

        $this->youtube = new \Google_Service_YouTube($this->client);
    }

    /**
     * Upload the video to YouTube
     *
     * @param string $path
     * @param array $data
     * @param string $privacyStatus
     *
     * @return \Google_Service_YouTube_Video
     *
     * @throws YoutubeException
     */
    public function upload(
        string $path,
        array $data = [],
        string $privacyStatus = PrivacyStatuses::PUBLIC
    ) : \Google_Service_YouTube_Video
    {
        if (!file_exists($path)) {
            throw new YoutubeException(
                "Video file does not exist at path: $path. Provide a full path to the file before attempting to upload."
            );
        }

        $fileSize = filesize($path);
        $video = $this->getVideo($data, $privacyStatus);
        // Set the Chunk Size
        $chunkSize = 1 * 1024 * 1024;
        if ($fileSize > $chunkSize) {
            // Set the defer to true
            $this->client->setDefer(true);
            // Build the request
            /** @var RequestInterface $request */
            $request = $this->youtube->videos->insert('status,snippet', $video);
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
     * @param array $data
     * @param string|null $id
     * @param string $privacyStatus
     *
     * @return \Google_Service_YouTube_Video
     *
     * @throws YoutubeException
     */
    public function update(
        array $data = [],
        string $id = null,
        string $privacyStatus = PrivacyStatuses::PUBLIC
    ) : \Google_Service_YouTube_Video
    {
        if (!$this->exists($id)) {
            throw new YoutubeException('A video matching id "' . $id . '" could not be found.');
        }

        $this->client->setDefer(false);
        $video = $this->getVideo($data, $privacyStatus, $id);
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
     * Create youtube playlist
     *
     * @param string $title
     * @param string $description
     * @param string $privacyStatus
     *
     * @return \Google_Service_YouTube_Playlist
     */
    public function createPlaylist(
        string $title,
        string $description,
        string $privacyStatus = PrivacyStatuses::PUBLIC
    ) : \Google_Service_YouTube_Playlist
    {
        // 1. Create the snippet for the playlist. Set its title and description.
        $playlistSnippet = new \Google_Service_YouTube_PlaylistSnippet();
        $playlistSnippet->setTitle($title);
        $playlistSnippet->setDescription($description);

        // 2. Define the playlist's status.
        $playlistStatus = new \Google_Service_YouTube_PlaylistStatus();
        $playlistStatus->setPrivacyStatus($privacyStatus);

        // 3. Define a playlist resource and associate the snippet and status
        // defined above with that resource.
        $youTubePlaylist = new \Google_Service_YouTube_Playlist();
        $youTubePlaylist->setSnippet($playlistSnippet);
        $youTubePlaylist->setStatus($playlistStatus);

        $this->client->setDefer(false);
        // 4. Call the playlists.insert method to create the playlist. The API
        // response will contain information about the new playlist.
        return $this->youtube->playlists->insert('snippet,status', $youTubePlaylist);
    }

    /**
     * Add video to playlist
     *
     * @param \Google_Service_YouTube_Playlist $playlist
     * @param \Google_Service_YouTube_Video $video
     *
     * @return \Google_Service_YouTube_PlaylistItem
     */
    public function addVideoToPlaylist(
        \Google_Service_YouTube_Playlist $playlist,
        \Google_Service_YouTube_Video $video
    ) : \Google_Service_YouTube_PlaylistItem
    {

        // Add a video to the playlist. First, define the resource being added
        // to the playlist by setting its video ID and kind.
        $resourceId = new \Google_Service_YouTube_ResourceId();
        $resourceId->setVideoId($video->getId());
        $resourceId->setKind($video->getKind());
        $resourceId->setPlaylistId($playlist->getId());

        // Then define a snippet for the playlist item. Set the playlist item's
        // title if you want to display a different value than the title of the
        // video being added. Add the resource ID and the playlist ID retrieved
        $playlistItemSnippet = new \Google_Service_YouTube_PlaylistItemSnippet();
        $playlistItemSnippet->setTitle($video->getSnippet()->getTitle());
        $playlistItemSnippet->setDescription($video->getSnippet()->getDescription());
        $playlistItemSnippet->setPlaylistId($playlist->getId());
        $playlistItemSnippet->setResourceId($resourceId);
        $playlistItemSnippet->setThumbnails($video->getSnippet()->getThumbnails());

        // Finally, create a playlistItem resource and add the snippet to the
        // resource, then call the playlistItems.insert method to add the playlist
        // item.
        $playlistItem = new \Google_Service_YouTube_PlaylistItem();
        $playlistItem->setSnippet($playlistItemSnippet);

        return $this->youtube->playlistItems->insert('snippet,contentDetails', $playlistItem);
    }


    /**
     * Create video object from array data
     *
     * @param array $data
     * @param string $privacyStatus
     * @param string|null $id
     *
     * @return \Google_Service_YouTube_Video
     */
    private function getVideo(
        array $data,
        string $privacyStatus = PrivacyStatuses::PUBLIC,
        string $id = null
    ) : \Google_Service_YouTube_Video
    {
        // Setup the Snippet
        $snippet = new \Google_Service_YouTube_VideoSnippet();

        if (array_key_exists('title', $data)) {
            $snippet->setTitle($data['title']);
        }
        if (array_key_exists('description', $data)) {
            $snippet->setDescription($data['description']);
        }
        if (array_key_exists('tags', $data)) {
            $snippet->setTags($data['tags']);
        }
        if (array_key_exists('category_id', $data)) {
            $snippet->setCategoryId($data['category_id']);
        }

        // Set the Privacy Status
        $status = new \Google_Service_YouTube_VideoStatus();
        $status->privacyStatus = $privacyStatus;

        // Set the Snippet & Status
        $video = new \Google_Service_YouTube_Video();
        if ($id) {
            $video->setId($id);
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
