<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 */

namespace SebLucas\Cops\Calibre;

use SebLucas\Cops\Pages\Page;

class Tag extends Base
{
    public const PAGE_ID = Page::ALL_TAGS_ID;
    public const PAGE_ALL = Page::ALL_TAGS;
    public const PAGE_DETAIL = Page::TAG_DETAIL;
    public const SQL_TABLE = "tags";
    public const SQL_COLUMNS = "tags.id as id, tags.name as name, count(*) as count";
    public const SQL_ALL_TAGS = "select {0} from tags, books_tags_link where tags.id = tag group by tags.id, tags.name order by tags.name";

    public $id;
    public $name;

    public function __construct($post, $database = null)
    {
        $this->id = $post->id;
        $this->name = $post->name;
        $this->databaseId = $database;
    }

    public function getUri()
    {
        return "?page=".self::PAGE_DETAIL."&id=$this->id";
    }

    public function getEntryId()
    {
        return self::PAGE_ID.":".$this->id;
    }

    public static function getCount($database = null)
    {
        // str_format (localize("tags.alphabetical", count(array))
        return parent::getCountGeneric(self::SQL_TABLE, self::PAGE_ID, self::PAGE_ALL, $database);
    }

    public static function getTagById($tagId, $database = null)
    {
        $result = parent::getDb($database)->prepare('select tags.id as id, tags.name as name from tags where tags.id = ?');
        $result->execute([$tagId]);
        if ($post = $result->fetchObject()) {
            return new Tag($post, $database);
        }
        return new Tag((object)['id' => null, 'name' => localize("tagword.none")], $database);
    }

    public static function getAllTags($database = null, $numberPerPage = null)
    {
        global $config;

        $sql = self::SQL_ALL_TAGS;
        $sortField = $config['calibre_database_field_sort'] ?? '';
        if (!empty($sortField)) {
            $sql = str_replace('tags.name', 'tags.' . $sortField, $sql);
        }

        return Base::getEntryArrayWithBookNumber($sql, self::SQL_COLUMNS, [], self::class, $database, $numberPerPage);
    }

    public static function getAllTagsByQuery($query, $n, $database = null, $numberPerPage = null)
    {
        $columns  = "tags.id as id, tags.name as name, (select count(*) from books_tags_link where tags.id = tag) as count";
        $sql = 'select {0} from tags where upper (tags.name) like ? {1} order by tags.name';
        [$totalNumber, $result] = parent::executeQuery($sql, $columns, "", ['%' . $query . '%'], $n, $database, $numberPerPage);
        $entryArray = [];
        while ($post = $result->fetchObject()) {
            $tag = new Tag($post, $database);
            array_push($entryArray, $tag->getEntry($post->count));
        }
        return [$entryArray, $totalNumber];
    }
}
