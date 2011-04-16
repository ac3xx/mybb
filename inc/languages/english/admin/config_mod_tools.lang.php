<?php
/**
 * MyBB 1.4 English Language Pack
 * Copyright � 2008 MyBB Group, All Rights Reserved
 * 
 * $Id: config_mod_tools.lang.php 5379 2011-02-21 11:06:42Z Tomm $
 */
 
$l['mod_tools'] = "Moderator Tools";

$l['thread_tools'] = "Thread Tools";
$l['thread_tools_desc'] = "Custom moderator tools allows you to create combinations of moderator actions that can be used on both threads and posts. These can then be used like the default tools when managing your forum. Here you can manage your custom thread tools.";

$l['add_thread_tool'] = "Add Thread Tool";
$l['add_new_thread_tool'] = "Add New Thread Tool";
$l['add_thread_tool_desc'] = "Here you can add a new custom thread moderation tool. This tool will be accessible from both inline thread moderation and from within threads themselves, listed with the default moderation tools.";

$l['post_tools'] = "Post Tools";
$l['post_tools_desc'] = "Custom moderator tools allows you to create combinations of moderator actions that can be used on both threads and posts. These can then be used like the default tools when managing your forum. Here you can manage your custom post tools.";

$l['add_post_tool'] = "Add Post Tool";
$l['add_new_post_tool'] = "Add New Post Tool";
$l['add_post_tool_desc'] = "Here you can add a new custom post moderation tool. This tool will be accessible from within threads themselves, listed with the default moderation tools.";

$l['edit_post_tool'] = "Edit Post Tool";
$l['edit_thread_tool'] = "Edit Thread Tool";

$l['no_thread_tools'] = "There are no thread tools setup on your forum.";
$l['no_post_tools'] = "There are no post tools setup on your forum.";

$l['confirm_thread_tool_deletion'] = "Are you sure you want to delete this thread tool?";
$l['confirm_post_tool_deletion'] = "Are you sure you want to delete this post tool?";

$l['success_post_tool_deleted'] = "The selected post moderation tool has been deleted successfully.";
$l['success_thread_tool_deleted'] = "The selected thread moderation tool has been deleted successfully.";

$l['error_invalid_post_tool'] = "The specified post tool does not exist.";
$l['error_invalid_thread_tool'] = "The specified thread tool does not exist.";

$l['general_options'] = "General Options";
$l['short_description'] = "Short Description";
$l['available_in_forums'] = "Available in forums";
$l['all_forums'] = "All forums";
$l['select_forums'] = "Select forums";
$l['save_thread_tool'] = "Save Thread Tool";

$l['title'] = "Title";

$l['thread_moderation'] = "Thread Moderation";
$l['approve_unapprove'] = "Approve/Unapprove thread?";

$l['no_change'] = "No Change";
$l['approve'] = "Approve";
$l['unapprove'] = "Unapprove";
$l['stick'] = "Stick";
$l['unstick'] = "Unstick";
$l['open'] = "Open";
$l['close'] = "Close";
$l['toggle'] = "Toggle";
$l['days'] = "Days";

$l['forum_to_move_to'] = "Forum to move to:";
$l['leave_redirect'] = "Leave redirect?";
$l['delete_redirect_after'] = "Delete redirect after";
$l['do_not_move_thread'] = "Do not move thread";
$l['do_not_copy_thread'] = "Do not copy thread";
$l['move_thread'] = "Move thread?";
$l['move_thread_desc'] = "If moving the thread(s), the \"delete redirect after... days\" is only to be filled in if a redirect will be left.";
$l['forum_to_copy_to'] = "Forum to copy to:";
$l['copy_thread'] = "Copy thread?";
$l['open_close_thread'] = "Open/close thread?";
$l['delete_thread'] = "Delete thread?";
$l['merge_thread'] = "Merge thread?";
$l['merge_thread_desc'] = "Only if used in inline moderation.";
$l['delete_poll'] = "Delete poll?";
$l['delete_redirects'] = "Delete redirects?";
$l['new_subject'] = "New subject?";
$l['new_subject_desc'] = "{subject} represents the original subject. {username} represents the moderator's username.";

$l['add_new_reply'] = "Add New Reply";
$l['add_new_reply_desc'] = "Leave blank for no reply.";
$l['reply_subject'] = "Reply subject.";
$l['reply_subject_desc'] = "Only used if a reply was made.<br />{subject} represents the original subject. {username} represents the moderator's username.";

$l['success_mod_tool_created'] = "The moderation tool has been created successfully..";
$l['success_mod_tool_updated'] = "The moderation tool has been updated successfully.";

$l['inline_post_moderation'] = "Inline Post Moderation";
$l['delete_posts'] = "Delete posts?";
$l['merge_posts'] = "Merge posts?";
$l['merge_posts_desc'] = "Only if used from inline moderation.";
$l['approve_unapprove_posts'] = "Approve/unapprove posts?";

$l['split_posts'] = "Split Posts";
$l['split_posts2'] = "Split posts?";
$l['do_not_split'] = "Do not split posts";
$l['split_to_same_forum'] = "Split to same forum";
$l['close_split_thread'] = "Close split thread?";
$l['stick_split_thread'] = "Stick split thread?";
$l['unapprove_split_thread'] = "Unapprove split thread?";
$l['split_thread_subject'] = "Split thread subject";
$l['split_thread_subject_desc'] = "{subject} represents the original subject. Only required if splitting posts.";
$l['add_new_split_reply'] = "Add reply to split thread";
$l['add_new_split_reply_desc'] = "Leave blank for no reply.";
$l['split_reply_subject'] = "Reply subject";
$l['split_reply_subject_desc'] = "Only used if a reply is made";
$l['save_post_tool'] = "Save Post Tool";

$l['error_missing_title'] = "Please enter a name for this tool.";
$l['error_missing_description'] = "Please enter a short description for this tool.";
$l['error_no_forums_selected'] = "Please select the forums in which this tool will be available.";
?>