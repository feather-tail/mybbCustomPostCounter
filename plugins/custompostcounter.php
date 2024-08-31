<?php
if (!defined('IN_MYBB')) die('Direct access not allowed.');

function custompostcounter_info()
{
    return [
        'name'          => 'Custom Post Counter',
        'description'   => 'Counts user posts in specific forums and updates the custom field.',
        'website'       => 'https://github.com/feather-tail',
        'author'        => 'feather-tail',
        'authorsite'    => '',
        'version'       => '1.0',
        'compatibility' => '18*',
        'guid'          => '',
    ];
}

function custompostcounter_install()
{
    global $db;

    if (!$db->field_exists('countCustomPost', 'users')) {
        $db->add_column('users', 'countCustomPost', "INT(11) NOT NULL DEFAULT 0");
    }

    $setting_group = [
        'name' => 'custompostcounter',
        'title' => 'Custom Post Counter Settings',
        'description' => 'Settings for the Custom Post Counter plugin',
        'disporder' => 5,
        'isdefault' => 0
    ];

    $gid = $db->insert_query('settinggroups', $setting_group);

    $setting_array = [
        'custompostcounter_forums' => [
            'title' => 'Tracked Forums',
            'description' => 'Enter the IDs of the forums to track, separated by commas.',
            'optionscode' => 'text',
            'value' => '1',
            'disporder' => 1,
            'gid' => $gid,
        ],
    ];

    foreach ($setting_array as $name => $setting) {
        $setting['name'] = $name;
        $db->insert_query('settings', $setting);
    }

    rebuild_settings();
}

function custompostcounter_uninstall()
{
    global $db;

    if ($db->field_exists('countCustomPost', 'users')) {
        $db->drop_column('users', 'countCustomPost');
    }

    $db->delete_query('settings', "name IN ('custompostcounter_forums')");
    $db->delete_query('settinggroups', "name = 'custompostcounter'");
    
    rebuild_settings();
}

function custompostcounter_is_installed()
{
    global $db;
    return $db->field_exists('countCustomPost', 'users');
}

function custompostcounter_activate()
{
    global $db;

    $template = $db->simple_select('templates', 'template', "title='postbit_author_user'");
    $template_content = $db->fetch_field($template, 'template');

    $new_template_content = str_replace(
        '{$post[\'replink\']}{$post[\'profilefield\']}{$post[\'warninglevel\']}',
        '{$post[\'replink\']}{$post[\'profilefield\']}{$post[\'warninglevel\']} <br /><strong>Custom Posts:</strong> {$post[\'countCustomPost\']}',
        $template_content
    );

    $db->update_query('templates', ['template' => $db->escape_string($new_template_content)], "title='postbit_author_user'");
}

function custompostcounter_deactivate()
{
    global $db;
 плагина
    $template = $db->simple_select('templates', 'template', "title='postbit_author_user'");
    $template_content = $db->fetch_field($template, 'template');

    $new_template_content = str_replace(
        '<br /><strong>Custom Posts:</strong> {$post[\'countCustomPost\']}',
        '',
        $template_content
    );

    $db->update_query('templates', ['template' => $db->escape_string($new_template_content)], "title='postbit_author_user'");
}

function custompostcounter_get_valid_forums()
{
    global $mybb;
    $forums = $mybb->settings['custompostcounter_forums'];
    return array_map('intval', explode(',', $forums));
}

$plugins->add_hook('datahandler_post_insert_post', 'custompostcounter_increment');
$plugins->add_hook('class_moderation_delete_post', 'custompostcounter_decrement');
$plugins->add_hook('class_moderation_soft_delete_posts', 'custompostcounter_decrement_soft');
$plugins->add_hook('class_moderation_restore_posts', 'custompostcounter_increment_soft');
$plugins->add_hook('class_moderation_delete_thread_start', 'custompostcounter_decrement_thread');
$plugins->add_hook('class_moderation_soft_delete_threads', 'custompostcounter_decrement_soft_thread');
$plugins->add_hook('class_moderation_restore_threads', 'custompostcounter_increment_soft_thread');

function custompostcounter_update_post_count($uid, $count)
{
    global $db;
    $db->write_query("
      UPDATE ".TABLE_PREFIX."users 
      SET countCustomPost = countCustomPost + {$count}
      WHERE uid = '{$uid}'
    ");
}

function custompostcounter_increment($post)
{
    if (in_array($post->data['fid'], custompostcounter_get_valid_forums())) {
        custompostcounter_update_post_count($post->data['uid'], 1);
    }
}

function custompostcounter_decrement($pid)
{
    global $db;
    $post = get_post($pid);
    $thread = get_thread($post['tid']);
    if ($post['pid'] == $thread['firstpost']) {
        return;
    } else {
        if (in_array($post['fid'], custompostcounter_get_valid_forums())) {
            custompostcounter_update_post_count($post['uid'], -1);
        }
    }
}

function custompostcounter_decrement_soft($pids)
{
    if (!is_array($pids)) $pids = array($pids);

    foreach ($pids as $pid) {
        $post = get_post($pid);
        $thread = get_thread($post['tid']);
        if ($post['pid'] == $thread['firstpost']) {
            custompostcounter_decrement_soft_thread($post['tid']);
        } else {
            if (in_array($post['fid'], custompostcounter_get_valid_forums())) {
                custompostcounter_update_post_count($post['uid'], -1);
            }
        }
    }
}

function custompostcounter_increment_soft($pids)
{
    if (!is_array($pids)) $pids = array($pids);

    foreach ($pids as $pid) {
        $post = get_post($pid);
        if (in_array($post['fid'], custompostcounter_get_valid_forums())) {
            custompostcounter_update_post_count($post['uid'], 1);
        }
    }
}

function custompostcounter_update_thread_post_count($tid, $count)
{
    global $db;
    $thread = get_thread($tid);
    if (in_array($thread['fid'], custompostcounter_get_valid_forums())) {
        $query = $db->simple_select("posts", "uid, COUNT(*) as post_count", "tid='{$tid}' AND pid != '{$thread['firstpost']}'", ["group_by" => "uid"]);
        while ($post = $db->fetch_array($query)) {
            custompostcounter_update_post_count($post['uid'], $count * $post['post_count']);
        }
    }
}

function custompostcounter_decrement_thread($tid)
{
    custompostcounter_update_thread_post_count($tid, -1);
}

function custompostcounter_decrement_soft_thread($tids)
{
    if (!is_array($tids)) $tids = array($tids);
    foreach ($tids as $tid) {
        custompostcounter_update_thread_post_count($tid, -1);
    }
}

function custompostcounter_increment_soft_thread($tids)
{
    if (!is_array($tids)) $tids = array($tids);
    foreach ($tids as $tid) {
        custompostcounter_update_thread_post_count($tid, 1);
    }
}
?>
