<?php
/**
 * Document Storage
 *
 * PHP Version 5
 *
 * @author   Richard Seymour <web@bespoke.support>
 * @license  MIT
 * @link     https://github.com/BespokeSupport/DocumentStorage
 */

namespace BespokeSupport\DocumentStorage;

use BespokeSupport\DatabaseWrapper\AbstractDatabaseWrapper;
use BespokeSupport\Mime\FileMimes;

/**
 * Class DocumentStorage
 * @package BespokeSupport\DocumentStorage
 */
class DocumentStorage
{
    /**
     * @param $contents
     * @return EntityFile
     */
    public static function contentsToFile($contents)
    {
        $tempName = tempnam(sys_get_temp_dir(), 'doc_store_temp_');
        file_put_contents($tempName, $contents);
        $entity = new EntityFile($tempName);
        $entity = self::entityPopulate($entity);

        return $entity;
    }

    /**
     * @param EntityFile $file
     * @return EntityFile
     */
    public static function entityPopulate(EntityFile $file)
    {
        $file->hash = self::splToHash($file);

        $fInfoClass = new \finfo(FILEINFO_MIME_TYPE | FILEINFO_PRESERVE_ATIME);
        $mime_type = $fInfoClass->buffer(file_get_contents($file->getRealPath()));
        $file->mime = $mime_type;

        if (!$file->extension && $file->mime) {
            $mimes = new FileMimes();
            $extension = $mimes->getExtensionFromMime($file->mime);
            if ($extension) {
                $file->extension = $extension;
            }
        }

        return $file;
    }

    /**
     * @param $path
     * @param EntityFile $file
     * @param AbstractDatabaseWrapper|null $database
     * @return EntityFile|null
     * @throws \Exception
     */
    public static function entitySave($path, EntityFile $file, AbstractDatabaseWrapper $database = null)
    {
        if (!is_writeable($path)) {
            throw new \Exception('Not Writable');
        }

        $savePath = $path . DIRECTORY_SEPARATOR . $file->hash;

        if ($file->extension) {
            $savePath .= '.' . $file->extension;
        }

        $fileInfo = $file->getFileInfo();
        $from = $fileInfo->getRealPath();
        $success = copy($from, $savePath);
        if (!$success) {
            return null;
        }

        $return = new EntityFile($savePath);
        $return = self::entityPopulate($return);
        $return->name = $file->name;

        $now = new \DateTime();
        $nowString = $now->format('Y-m-d H:i:s');

        if ($database) {
            $entity = $database->findOneBy(
                'document_storage_file',
                ['hash' => $return->hash]
            );
            if ($entity) {
                $return->id = $entity->id;
            } else {
                $return->id = $database->insert(
                    'document_storage_file',
                    [
                        'file_extension_original' => null,
                        'file_name_original' => null,
                        'created' => $nowString,
                        'file_size' => $return->getSize(),
                        'file_extension' => $return->extension,
                        'file_mime_type' => $return->mime,
                        'hash' => $return->hash,
                        'file_name' => $file->name,
                    ]
                );
            }
        }

        return $return;
    }

    /**
     * @param AbstractDatabaseWrapper $database
     * @param \SplFileInfo $file
     * @return null|object
     */
    public static function existsDatabaseFile(AbstractDatabaseWrapper $database, \SplFileInfo $file)
    {
        $hash = self::splToHash($file);

        return self::existsDatabaseFileHash($database, $hash);
    }

    /**
     * @param AbstractDatabaseWrapper $database
     * @param $hash
     * @param string $table
     * @return object|null
     */
    public static function existsDatabaseFileHash(
        AbstractDatabaseWrapper $database,
        $hash,
        $table = 'document_storage_file'
    ) {
        return $database->findOneBy(
            $table,
            [
                'hash' => $hash
            ]
        );
    }

    /**
     * TODO - Different strategies - id / hash sub directories
     *
     * @param string $baseDir
     * @return string
     */
    public static function getStoragePath($baseDir)
    {
        return $baseDir;
    }

    /**
     * @param \SplFileInfo $fileInfo
     * @return EntityFile
     */
    public static function splToEntity(\SplFileInfo $fileInfo)
    {
        $file = new EntityFile($fileInfo);

        $file = self::entityPopulate($file);

        return $file;
    }

    /**
     * @param \SplFileInfo $fileInfo
     * @param string $algorithm
     * @return string
     */
    public static function splToHash(\SplFileInfo $fileInfo, $algorithm = 'md5')
    {
        $path = $fileInfo->getRealPath();
        $fileHash = hash_file($algorithm, $path);

        return $fileHash;
    }
}
