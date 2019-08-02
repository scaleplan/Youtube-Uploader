<?php

namespace Scaleplan\Youtube;

use Scaleplan\Youtube\Constants\PrivacyStatuses;

/**
 * Class Playlist
 *
 * @package Scaleplan\Youtube
 */
class Playlist extends AbstractApi
{
    /**
     * Create youtube playlist
     *
     * @param string $title
     * @param string $description
     * @param string $privacyStatus
     * @param string|null $channelId
     *
     * @return \Google_Service_YouTube_Playlist
     */
    public function createPlaylist(
        string $title,
        string $description,
        string $privacyStatus = PrivacyStatuses::PUBLIC,
        string $channelId = null
    ) : \Google_Service_YouTube_Playlist
    {
        // 1. Create the snippet for the playlist. Set its title and description.
        $snippet = new \Google_Service_YouTube_PlaylistSnippet();
        $snippet->setTitle($title);
        $snippet->setDescription($description);
        if ($channelId) {
            $snippet->setChannelId($channelId);
        }

        // 2. Define the playlist's status.
        $status = new \Google_Service_YouTube_PlaylistStatus();
        $status->setPrivacyStatus($privacyStatus);

        // 3. Define a playlist resource and associate the snippet and status
        // defined above with that resource.
        $youTubePlaylist = new \Google_Service_YouTube_Playlist();
        $youTubePlaylist->setSnippet($snippet);
        $youTubePlaylist->setStatus($status);

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
}
