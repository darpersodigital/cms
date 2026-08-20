<?php

namespace Darpersodigital\Cms\Services;

class RelationLinkServices
{
    /**
     * Build the show url of a related record (the target of a "select" /
     * "select multiple" field), or null when it should be rendered as plain text.
     *
     * Returns null when:
     *  - the related post type does not exist (or has no route)
     *  - the related post type has no show screen
     *  - the logged in admin has no "read" permission on it
     */
    public static function showUrl($database_table, $id)
    {
        if (!$database_table || !$id) return null;

        $post_type = self::findPostType($database_table);

        if (!$post_type) return null;
        if (empty($post_type['route'])) return null;
        if (empty($post_type['show'])) return null;
        if (empty($post_type['permissions']['read'])) return null;

        return url(config('cms_config.route_path_prefix') . '/' . $post_type['route'] . '/' . $id);
    }

    /**
     * Look up a post type (with the permissions computed by AdminMiddleware)
     * by its database table.
     */
    private static function findPostType($database_table)
    {
        $admin = request()->get('admin');

        foreach ($admin['post_types'] ?? [] as $post_type) {
            if (($post_type['database_table'] ?? null) === $database_table) return $post_type;
        }

        return null;
    }
}
