<?php
/**
 * MyBB 1.4
 * Copyright � 2008 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybboard.net
 * License: http://www.mybboard.net/about/license
 *
 * $Id: moderation_queue.php 5379 2011-02-21 11:06:42Z Tomm $
 */

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$page->add_breadcrumb_item($lang->moderation_queue, "index.php?module=forum/moderation_queue");

$sub_tabs['threads'] = array(
	'title' => $lang->threads,
	'link' => "index.php?module=forum/moderation_queue&amp;type=threads",
	'description' => $lang->threads_desc
);

$sub_tabs['posts'] = array(
	'title' => $lang->posts,
	'link' => "index.php?module=forum/moderation_queue&amp;type=posts",
	'description' => $lang->posts_desc
);

$sub_tabs['attachments'] = array(
	'title' => $lang->attachments,
	'link' => "index.php?module=forum/moderation_queue&amp;type=attachments",
	'description' => $lang->attachments_desc
);

$plugins->run_hooks("admin_forum_moderation_queue_begin");

// Actually performing our moderation choices
if($mybb->request_method == "post")
{
	$plugins->run_hooks("admin_forum_moderation_queue_commit");
	
	require_once MYBB_ROOT."inc/functions_upload.php";
	require_once MYBB_ROOT."inc/class_moderation.php";
	$moderation = new Moderation;

	if(is_array($mybb->input['threads']))
	{
		// Fetch threads
		$query = $db->simple_select("threads", "tid", "tid IN (".implode(",", array_map("intval", array_keys($mybb->input['threads'])))."){$flist}");
		while($thread = $db->fetch_array($query))
		{
			$action = $mybb->input['threads'][$thread['tid']];
			if($action == "approve")
			{
				$threads_to_approve[] = $thread['tid'];
			}
			else if($action == "delete")
			{
				$moderation->delete_thread($thread['tid']);
			}
		}
		if(is_array($threads_to_approve))
		{
			$moderation->approve_threads($threads_to_approve);
		}
		
		$plugins->run_hooks("admin_forum_moderation_queue_threads_commit");

		// Log admin action
		log_admin_action('threads');

		flash_message($lang->success_threads, 'success');
		admin_redirect("index.php?module=forum/moderation_queue&type=threads");
	}
	else if(is_array($mybb->input['posts']))
	{
		// Fetch posts
		$query = $db->simple_select("posts", "pid", "pid IN (".implode(",", array_map("intval", array_keys($mybb->input['posts'])))."){$flist}");
		while($post = $db->fetch_array($query))
		{
			$action = $mybb->input['posts'][$post['pid']];
			if($action == "approve")
			{
				$posts_to_approve[] = $post['pid'];
			}
			else if($action == "delete")
			{
				$moderation->delete_post($post['pid']);
			}
		}
		if(is_array($posts_to_approve))
		{
			$moderation->approve_posts($posts_to_approve);
		}
		
		$plugins->run_hooks("admin_forum_moderation_queue_posts_commit");

		// Log admin action
		log_admin_action('posts');

		flash_message($lang->success_posts, 'success');
		admin_redirect("index.php?module=forum/moderation_queue&type=posts");

	}
	else if(is_array($mybb->input['attachments']))
	{
		$query = $db->simple_select("attachments", "aid, pid", "aid IN (".implode(",", array_map("intval", array_keys($mybb->input['attachments'])))."){$flist}");
		while($attachment = $db->fetch_array($query))
		{
			$action = $mybb->input['attachments'][$attachment['aid']];
			if($action == "approve")
			{
				$db->update_query("attachments", array("visible" => 1), "aid='{$attachment['aid']}'");
			}
			else if($action == "delete")
			{
				remove_attachment($attachment['pid'], '', $attachment['aid']);
			}
		}
		
		$plugins->run_hooks("admin_forum_moderation_queue_attachments_commit");

		// Log admin action
		log_admin_action('attachments');

		flash_message($lang->success_attachments, 'success');
		admin_redirect("index.php?module=forum/moderation_queue&type=attachments");
	}
}

$all_options = "<ul class=\"modqueue_mass\">\n";
$all_options .= "<li><a href=\"#\" class=\"mass_ignore\" onclick=\"$$('input.radio_ignore').each(function(e) { e.checked = true; }); return false;\">{$lang->mark_as_ignored}</a></li>\n";
$all_options .= "<li><a href=\"#\" class=\"mass_delete\" onclick=\"$$('input.radio_delete').each(function(e) { e.checked = true; }); return false;\">{$lang->mark_as_deleted}</a></li>\n";
$all_options .= "<li><a href=\"#\" class=\"mass_approve\" onclick=\"$$('input.radio_approve').each(function(e) { e.checked = true; }); return false;\">{$lang->mark_as_approved}</a></li>\n";
$all_options .= "</ul>\n";

// Threads awaiting moderation
if($mybb->input['type'] == "threads" || !$mybb->input['type'])
{
	$plugins->run_hooks("admin_forum_moderation_queue_threads");
	
	$forum_cache = $cache->read("forums");

	$query = $db->simple_select("threads", "COUNT(tid) AS unapprovedthreads", "visible=0");
	$unapproved_threads = $db->fetch_field($query, "unapprovedthreads");

	if($unapproved_threads > 0)
	{
		// Figure out if we need to display multiple pages.
		$per_page = 15;
		if($mybb->input['page'] > 0)
		{
			$current_page = intval($mybb->input['page']);
			$start = ($current_page-1)*$per_page;
			$pages = $unaproved_threads / $per_page;
			$pages = ceil($pages);
			if($current_page > $pages)
			{
				$start = 0;
				$current_page = 1;
			}
		}
		else
		{
			$start = 0;
			$current_page = 1;
		}

		$pagination = draw_admin_pagination($current_page, $per_page, $unaproved_threads, "index.php?module=forum/moderation_queue&amp;page={page}");

		$page->add_breadcrumb_item($lang->threads_awaiting_moderation);
		$page->output_header($lang->threads_awaiting_moderation);
		$page->output_nav_tabs($sub_tabs, "threads");

		$form = new Form("index.php?module=forum/moderation_queue", "post");

		$table = new Table;
		$table->construct_header($lang->subject);
		$table->construct_header($lang->author, array("class" => "align_center", "width" => "20%"));
		$table->construct_header($lang->posted, array("class" => "align_center", "width" => "20%"));

		$query = $db->query("
			SELECT t.tid, t.dateline, t.fid, t.subject, p.message AS postmessage, u.username AS username, t.uid
			FROM ".TABLE_PREFIX."threads t
			LEFT JOIN ".TABLE_PREFIX."posts p ON (p.pid=t.firstpost)
			LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid=t.uid)
			WHERE t.visible='0'
			ORDER BY t.lastpost DESC
			LIMIT {$start}, {$per_page}
		");
		while($thread = $db->fetch_array($query))
		{
			$thread['subject'] = htmlspecialchars_uni($thread['subject']);
			$thread['threadlink'] = get_thread_link($thread['tid']);
			$thread['forumlink'] = get_forum_link($thread['fid']);
			$forum_name = $forum_cache[$thread['fid']]['name'];
			$threaddate = my_date($mybb->settings['dateformat'], $thread['dateline']);
			$threadtime = my_date($mybb->settings['timeformat'], $thread['dateline']);
			$profile_link = build_profile_link($thread['username'], $thread['uid']);
			$thread['postmessage'] = nl2br(htmlspecialchars_uni($thread['postmessage']));

			$table->construct_cell("<a href=\"../{$thread['threadlink']}\">{$thread['subject']}</a>");
			$table->construct_cell($profile_link, array("class" => "align_center"));
			$table->construct_cell("{$threaddate}, {$threadtime}", array("class" => "align_center"));
			$table->construct_row();

			$controls = "<div class=\"modqueue_controls\">\n";
			$controls .= $form->generate_radio_button("threads[{$thread['tid']}]", "ignore", $lang->ignore, array('class' => 'radio_ignore', 'checked' => true))." ";
			$controls .= $form->generate_radio_button("threads[{$thread['tid']}]", "delete", $lang->delete, array('class' => 'radio_delete', 'checked' => false))." ";
			$controls .= $form->generate_radio_button("threads[{$thread['tid']}]", "approve", $lang->approve, array('class' => 'radio_approve', 'checked' => false));
			$controls .= "</div>";

			$forum = "<strong>{$lang->forum} <a href=\"../{$thread['forumlink']}\">{$forum_name}</a></strong><br />";

			$table->construct_cell("<div class=\"modqueue_message\">{$controls}<div class=\"modqueue_meta\">{$forum}</div>{$thread['postmessage']}</div>", array("colspan" => 3));
			$table->construct_row();
		}

		$table->output($lang->threads_awaiting_moderation);
		echo $all_options;
		echo $pagination;

		$buttons[] = $form->generate_submit_button($lang->perform_action);
		$form->output_submit_wrapper($buttons);
		$form->end();
		$page->output_footer();
	}
}

// Posts awaiting moderation
if($mybb->input['type'] == "posts" || $mybb->input['type'] == "")
{
	$plugins->run_hooks("admin_forum_moderation_queue_posts");
	
	$forum_cache = $cache->read("forums");

	$query = $db->query("
		SELECT COUNT(pid) AS unapprovedposts
		FROM  ".TABLE_PREFIX."posts p
		LEFT JOIN ".TABLE_PREFIX."threads t ON (t.tid=p.tid)
		WHERE p.visible='0' AND t.firstpost != p.pid
	");
	$unapproved_posts = $db->fetch_field($query, "unapprovedposts");

	if($unapproved_posts > 0)
	{
		// Figure out if we need to display multiple pages.
		$per_page = 15;
		if($mybb->input['page'] > 0)
		{
			$current_page = intval($mybb->input['page']);
			$start = ($current_page-1)*$per_page;
			$pages = $unaproved_posts / $per_page;
			$pages = ceil($pages);
			if($current_page > $pages)
			{
				$start = 0;
				$current_page = 1;
			}
		}
		else
		{
			$start = 0;
			$current_page = 1;
		}

		$pagination = draw_admin_pagination($current_page, $per_page, $unaproved_posts, "index.php?module=forum/moderation_queue&amp;type=posts&amp;page={page}");


		$page->add_breadcrumb_item($lang->posts_awaiting_moderation);
		$page->output_header($lang->posts_awaiting_moderation);
		$page->output_nav_tabs($sub_tabs, "posts");

		$form = new Form("index.php?module=forum/moderation_queue", "post");

		$table = new Table;
		$table->construct_header($lang->subject);
		$table->construct_header($lang->author, array("class" => "align_center", "width" => "20%"));
		$table->construct_header($lang->posted, array("class" => "align_center", "width" => "20%"));

		$query = $db->query("
			SELECT p.pid, p.subject, p.message, t.subject AS threadsubject, t.tid, u.username, p.uid, t.fid
			FROM  ".TABLE_PREFIX."posts p
			LEFT JOIN ".TABLE_PREFIX."threads t ON (t.tid=p.tid)
			LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid=p.uid)
			WHERE p.visible='0' AND t.firstpost != p.pid
			ORDER BY p.dateline DESC
			LIMIT {$start}, {$per_page}
		");
		while($post = $db->fetch_array($query))
		{
			$altbg = alt_trow();
			$post['threadsubject'] = htmlspecialchars_uni($post['threadsubject']);
			$post['subject'] = htmlspecialchars_uni($post['subject']);
			
			if(!$post['subject'])
			{
				$post['subject'] = $lang->re." ".$post['threadsubject'];
			}

			$post['postlink'] = get_post_link($post['pid'], $post['tid']);
			$post['threadlink'] = get_thread_link($post['tid']);
			$post['forumlink'] = get_forum_link($post['fid']);
			$forum_name = $forum_cache[$post['fid']]['name'];
			$postdate = my_date($mybb->settings['dateformat'], $post['dateline']);
			$posttime = my_date($mybb->settings['timeformat'], $post['dateline']);
			$profile_link = build_profile_link($post['username'], $post['uid']);
			$post['message'] = nl2br(htmlspecialchars_uni($post['message']));

			$table->construct_cell("<a href=\"../{$post['postlink']}#pid{$post['pid']}\">{$post['subject']}</a>");
			$table->construct_cell($profile_link, array("class" => "align_center"));
			$table->construct_cell("{$postdate}, {$posttime}", array("class" => "align_center"));
			$table->construct_row();

			$controls = "<div class=\"modqueue_controls\">\n";
			$controls .= $form->generate_radio_button("posts[{$post['pid']}]", "ignore", $lang->ignore, array('class' => 'radio_ignore', 'checked' => true))." ";
			$controls .= $form->generate_radio_button("posts[{$post['pid']}]", "delete",$lang->delete, array('class' => 'radio_delete', 'checked' => false))." ";
			$controls .= $form->generate_radio_button("posts[{$post['pid']}]", "approve", $lang->approve, array('class' => 'radio_approve', 'checked' => false));
			$controls .= "</div>";

			$thread = "<strong>{$lang->thread} <a href=\"../{$post['threadlink']}\">{$post['threadsubject']}</a></strong>";
			$forum = "<strong>{$lang->forum} <a href=\"../{$post['forumlink']}\">{$forum_name}</a></strong><br />";

			$table->construct_cell("<div class=\"modqueue_message\">{$controls}<div class=\"modqueue_meta\">{$forum}{$thread}</div>{$post['message']}</div>", array("colspan" => 3));
			$table->construct_row();
		}

		$table->output($lang->posts_awaiting_moderation);
		echo $all_options;
		echo $pagination;

		$buttons[] = $form->generate_submit_button($lang->perform_action);
		$form->output_submit_wrapper($buttons);
		$form->end();
		$page->output_footer();
	}
	else if($mybb->input['type'] == "posts")
	{
		$page->output_header($lang->moderation_queue);
		$page->output_nav_tabs($sub_tabs, "posts");
		echo "<p class=\"notice\">{$lang->error_no_posts}</p>";
		$page->output_footer();
	}
}

// Attachments awaiting moderation
if($mybb->input['type'] == "attachments" || $mybb->input['type'] == "")
{
	$plugins->run_hooks("admin_forum_moderation_queue_attachments");
	
	$query = $db->query("
		SELECT COUNT(aid) AS unapprovedattachments
		FROM  ".TABLE_PREFIX."attachments a
		LEFT JOIN ".TABLE_PREFIX."posts p ON (p.pid=a.pid)
		LEFT JOIN ".TABLE_PREFIX."threads t ON (t.tid=p.tid)
		WHERE a.visible='0'
	");
	$unapproved_attachments = $db->fetch_field($query, "unapprovedattachments");

	if($unapproved_attachments > 0)
	{
		// Figure out if we need to display multiple pages.
		$per_page = 15;
		if($mybb->input['page'] > 0)
		{
			$current_page = intval($mybb->input['page']);
			$start = ($current_page-1)*$per_page;
			$pages = $unapproved_attachments / $per_page;
			$pages = ceil($pages);
			if($current_page > $pages)
			{
				$start = 0;
				$current_page = 1;
			}
		}
		else
		{
			$start = 0;
			$current_page = 1;
		}

		$pagination = draw_admin_pagination($current_page, $per_page, $unapproved_attachments, "index.php?module=forum/moderation_queue&amp;type=attachments&amp;page={page}");

		$page->add_breadcrumb_item($lang->attachments_awaiting_moderation);
		$page->output_header($lang->attachments_awaiting_moderation);
		$page->output_nav_tabs($sub_tabs, "attachments");

		$form = new Form("index.php?module=forum/moderation_queue", "post");

		$table = new Table;
		$table->construct_header($lang->filename);
		$table->construct_header($lang->uploadedby, array("class" => "align_center", "width" => "20%"));
		$table->construct_header($lang->posted, array("class" => "align_center", "width" => "20%"));
		$table->construct_header($lang->controls, array("class" => "align_center", "colspan" => 3));

		$query = $db->query("
			SELECT a.*, p.subject AS postsubject, p.dateline, p.uid, u.username, t.tid, t.subject AS threadsubject
			FROM  ".TABLE_PREFIX."attachments a
			LEFT JOIN ".TABLE_PREFIX."posts p ON (p.pid=a.pid)
			LEFT JOIN ".TABLE_PREFIX."threads t ON (t.tid=p.tid)
			LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid=p.uid)
			WHERE a.visible='0'
			ORDER BY a.dateuploaded DESC
			LIMIT {$start}, {$per_page}
		");

		while($attachment = $db->fetch_array($query))
		{
			if(!$attachment['dateuploaded']) $attachment['dateuploaded'] = $attachment['dateline'];
			$attachdate = my_date($mybb->settings['dateformat'], $attachment['dateuploaded']);
			$attachtime = my_date($mybb->settings['timeformat'], $attachment['dateuploaded']);

			$attachment['postsubject'] = htmlspecialchars_uni($attachment['postsubject']);
			$attachment['filename'] = htmlspecialchars_uni($attachment['filename']);
			$attachment['threadsubject'] = htmlspecialchars_uni($attachment['threadsubject']);
			$attachment['filesize'] = get_friendly_size($attachment['filesize']);

			$link = get_post_link($attachment['pid'], $attachment['tid']) . "#pid{$attachment['pid']}";
			$thread_link = get_thread_link($attachment['tid']);
			$profile_link = build_profile_link($attachment['username'], $attachment['uid']);

			$table->construct_cell("<a href=\"../attachment.php?aid={$attachment['aid']}\" target=\"_blank\">{$attachment['filename']}</a> ({$attachment['filesize']})<br /><small class=\"modqueue_meta\">{$lang->post} <a href=\"{$link}\">{$attachment['postsubject']}</a></small>");
			$table->construct_cell($profile_link, array("class" => "align_center"));
			$table->construct_cell("{$attachdate}, {$attachtime}", array("class" => "align_center"));

			$table->construct_cell($form->generate_radio_button("attachments[{$attachment['aid']}]", "ignore", $lang->ignore, array('class' => 'radio_ignore', 'checked' => true)), array("class" => "align_center"));
			$table->construct_cell($form->generate_radio_button("attachments[{$attachment['aid']}]", "delete", $lang->delete, array('class' => 'radio_delete', 'checked' => false)), array("class" => "align_center"));
			$table->construct_cell($form->generate_radio_button("attachments[{$attachment['aid']}]", "approve", $lang->approve, array('class' => 'radio_approve', 'checked' => false)), array("class" => "align_center"));
			$table->construct_row();
		}
		$table->output($lang->attachments_awaiting_moderation);
		echo $all_options;
		echo $pagination;

		$buttons[] = $form->generate_submit_button($lang->perform_action);
		$form->output_submit_wrapper($buttons);
		$form->end();
		$page->output_footer();
	}
	else if($mybb->input['type'] == "attachments")
	{
		$page->output_header($lang->moderation_queue);
		$page->output_nav_tabs($sub_tabs, "attachments");
		echo "<p class=\"notice\">{$lang->error_no_attachments}</p>";
		$page->output_footer();
	}
}

// Still nothing? All queues are empty! :-D
$page->output_header($lang->moderation_queue);
echo "<p class=\"notice\">{$lang->error_no_threads}</p>";
$page->output_footer();

?>