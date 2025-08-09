<?php

class ImageHandler {
    public function validateImageUrl($url) {
        if (empty($url)) return false;
        
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];
        $urlPath = strtolower(parse_url($url, PHP_URL_PATH));
        
        foreach ($imageExtensions as $ext) {
            if (str_ends_with($urlPath, $ext)) {
                return true;
            }
        }
        
        return false;
    }
}
