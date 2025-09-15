<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;

trait EncryptedAttachment
{
    /**
     * Encrypt file content and store it
     */
    public function storeEncrypted(string $content, string $path): bool
    {
        $encryptedContent = Crypt::encrypt($content);
        return uploadDisk()->put($path, $encryptedContent);
    }

    /**
     * Retrieve and decrypt file content
     */
    public function getDecrypted(): string
    {
        if (!uploadDisk()->exists($this->storage_path)) {
            throw new \RuntimeException("Encrypted file not found: {$this->storage_path}");
        }

        $encryptedContent = uploadDisk()->get($this->storage_path);
        
        try {
            return Crypt::decrypt($encryptedContent);
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to decrypt file: {$this->storage_path}", 0, $e);
        }
    }

}