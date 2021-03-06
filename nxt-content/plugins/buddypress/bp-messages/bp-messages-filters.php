<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/* Apply NXTClass defined filters */
add_filter( 'bp_get_message_notice_subject', 'nxt_filter_kses', 1 );
add_filter( 'bp_get_message_notice_text', 'nxt_filter_kses', 1 );
add_filter( 'bp_get_message_thread_subject', 'nxt_filter_kses', 1 );
add_filter( 'bp_get_message_thread_excerpt', 'nxt_filter_kses', 1 );
add_filter( 'bp_get_messages_subject_value', 'nxt_filter_kses', 1 );
add_filter( 'bp_get_messages_content_value', 'nxt_filter_kses', 1 );
add_filter( 'bp_get_the_thread_message_content', 'nxt_filter_kses', 1 );

add_filter( 'messages_message_content_before_save', 'nxt_filter_kses', 1 );
add_filter( 'messages_message_subject_before_save', 'nxt_filter_kses', 1 );
add_filter( 'messages_notice_message_before_save', 'nxt_filter_kses', 1 );
add_filter( 'messages_notice_subject_before_save', 'nxt_filter_kses', 1 );

add_filter( 'bp_get_the_thread_message_content', 'nxt_filter_kses', 1 );
add_filter( 'bp_get_the_thread_subject', 'nxt_filter_kses', 1 );

add_filter( 'messages_message_content_before_save', 'force_balance_tags' );
add_filter( 'messages_message_subject_before_save', 'force_balance_tags' );
add_filter( 'messages_notice_message_before_save', 'force_balance_tags' );
add_filter( 'messages_notice_subject_before_save', 'force_balance_tags' );

add_filter( 'bp_get_message_notice_subject', 'nxttexturize' );
add_filter( 'bp_get_message_notice_text', 'nxttexturize' );
add_filter( 'bp_get_message_thread_subject', 'nxttexturize' );
add_filter( 'bp_get_message_thread_excerpt', 'nxttexturize' );
add_filter( 'bp_get_the_thread_message_content', 'nxttexturize' );

add_filter( 'bp_get_message_notice_subject', 'convert_smilies', 2 );
add_filter( 'bp_get_message_notice_text', 'convert_smilies', 2 );
add_filter( 'bp_get_message_thread_subject', 'convert_smilies', 2 );
add_filter( 'bp_get_message_thread_excerpt', 'convert_smilies', 2 );
add_filter( 'bp_get_the_thread_message_content', 'convert_smilies', 2 );

add_filter( 'bp_get_message_notice_subject', 'convert_chars' );
add_filter( 'bp_get_message_notice_text', 'convert_chars' );
add_filter( 'bp_get_message_thread_subject', 'convert_chars' );
add_filter( 'bp_get_message_thread_excerpt', 'convert_chars' );
add_filter( 'bp_get_the_thread_message_content', 'convert_chars' );

add_filter( 'bp_get_message_notice_text', 'make_clickable', 9 );
add_filter( 'bp_get_message_thread_excerpt', 'make_clickable', 9 );
add_filter( 'bp_get_the_thread_message_content', 'make_clickable', 9 );

add_filter( 'bp_get_message_notice_text', 'nxtautop' );
add_filter( 'bp_get_the_thread_message_content', 'nxtautop' );

add_filter( 'bp_get_message_notice_subject', 'stripslashes_deep' );
add_filter( 'bp_get_message_notice_text', 'stripslashes_deep' );
add_filter( 'bp_get_message_thread_subject', 'stripslashes_deep' );
add_filter( 'bp_get_message_thread_excerpt', 'stripslashes_deep' );
add_filter( 'bp_get_messages_subject_value', 'stripslashes_deep' );
add_filter( 'bp_get_messages_content_value', 'stripslashes_deep' );
add_filter( 'bp_get_the_thread_message_content', 'stripslashes_deep' );

add_filter( 'bp_get_the_thread_message_content', 'stripslashes_deep' );
add_filter( 'bp_get_the_thread_subject', 'stripslashes_deep' );

?>