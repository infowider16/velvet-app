<?php

$filePath = __DIR__ . '/app/Services/MessageService.php';

if (!file_exists($filePath)) {
    exit("File not found: {$filePath}\n");
}

$content = file_get_contents($filePath);

$replacements = [
    "'Group chat history retrieved successfully.'" => "__('message.group_chat_history_retrieved_successfully')",
    "'Failed to retrieve group chat history.'" => "__('message.failed_to_retrieve_group_chat_history')",
    "'Chat history retrieved successfully.'" => "__('message.chat_history_retrieved_successfully')",
    "'Failed to retrieve chat history.'" => "__('message.failed_to_retrieve_chat_history')",

    "'Message not found.'" => "__('message.message_not_found')",
    "'You can only delete your own messages.'" => "__('message.delete_only_own_message')",
    "'Failed to delete message.'" => "__('message.failed_to_delete_message')",
    "'Message deleted successfully.'" => "__('message.message_deleted_successfully')",

    "'Sent message users fetched successfully.'" => "__('message.sent_message_users_fetched_successfully')",
    "'Failed to fetch sent message users.'" => "__('message.failed_to_fetch_sent_message_users')",

    "'Group name and member IDs are required.'" => "__('message.group_name_and_member_ids_required')",
    "'Group created successfully.'" => "__('message.group_created_successfully')",

    "'Group ID is required.'" => "__('message.group_id_required')",
    "'Group not found.'" => "__('message.group_not_found')",
    "'Already a member of this group.'" => "__('message.already_member_group')",
    "'You cannot re-join this private group. Only admin can invite you again.'" => "__('message.cannot_rejoin_private_group')",
    "'Joined group successfully.'" => "__('message.joined_group_successfully')",
    "'Join request sent to group admin.'" => "__('message.join_request_sent')",

    "'Group ID, user ID, and action are required.'" => "__('message.group_user_action_required')",
    "'Only group admin can handle join requests.'" => "__('message.only_admin_handle_requests')",
    "'Join request accepted.'" => "__('message.join_request_accepted')",
    "'Join request rejected.'" => "__('message.join_request_rejected')",
    "'Invalid action.'" => "__('message.invalid_action')",

    "'All conversation messages deleted successfully.'" => "__('message.conversation_deleted_successfully')",

    "'Groups retrieved successfully.'" => "__('message.groups_retrieved_successfully')",
    "'Group name existence checked successfully.'" => "__('message.group_name_checked')",
    "'Groups fetched successfully.'" => "__('message.groups_fetched_successfully')",

    "'Members added to group successfully.'" => "__('message.members_added_successfully')",
    "'Only group admin can add members.'" => "__('message.only_admin_add_members')",
    "'No new members were added. All users are already members.'" => "__('message.no_new_members_added')",

    "'Group member blocked successfully.'" => "__('message.member_blocked_successfully')",
    "'Group member unblocked successfully.'" => "__('message.member_unblocked_successfully')",

    "'Permissions updated for all group members.'" => "__('message.group_permissions_updated')",
    "'Permission updated for group member.'" => "__('message.member_permission_updated')",
    "'Permission fetched for group member.'" => "__('message.member_permission_fetched')",

    "'Group messages and members fetched successfully.'" => "__('message.group_messages_members_fetched')",
    "'Group deleted successfully.'" => "__('message.group_deleted_successfully')",
    "'Group details fetched successfully.'" => "__('message.group_details_fetched')",
    "'Group updated successfully.'" => "__('message.group_updated_successfully')",
    "'Left group successfully.'" => "__('message.left_group_successfully')",

    "'Group reported successfully.'" => "__('message.group_reported_successfully')",
    "'User reported successfully.'" => "__('message.user_reported_successfully')",
    "'Pin reported successfully.'" => "__('message.pin_reported_successfully')",

    "'Media list fetched successfully.'" => "__('message.media_list_fetched')",
    "'Document list fetched successfully.'" => "__('message.document_list_fetched')",
    "'Link list fetched successfully.'" => "__('message.link_list_fetched')",

    "'Latest group messages fetched successfully.'" => "__('message.latest_group_messages_fetched')",
    "'Latest individual messages fetched successfully.'" => "__('message.latest_individual_messages_fetched')",

    "'Group chat deleted for your account only.'" => "__('message.group_chat_deleted_for_user')",
    "'All messages in this group have been permanently deleted by the admin.'" => "__('message.group_messages_deleted_by_admin')",

    "'Notification status fetched successfully.'" => "__('message.notification_status_fetched')",
    "'Notification status updated successfully.'" => "__('message.notification_status_updated')",
    "'No chat found to update notification status.'" => "__('message.no_chat_found_for_notification')",

    "'Group ID and user_ids array are required.'" => "__('message.group_id_and_user_ids_required')",
    "'Failed to add member(s) to group.'" => "__('message.failed_to_add_members')",

    "'Group ID and User ID are required.'" => "__('message.group_id_and_user_id_required')",
    "'Only group admin can remove members.'" => "__('message.only_admin_remove_members')",
    "'User is not a member of this group.'" => "__('message.user_not_member_of_group')",
    "'Admin cannot remove themselves from the group.'" => "__('message.admin_cannot_remove_self')",
    "'Failed to remove member from group.'" => "__('message.failed_to_remove_member_from_group')",
    "'Member removed from group successfully.'" => "__('message.member_removed_successfully')",

    "'Only group admin can perform this action.'" => "__('message.only_admin_perform_action')",
    "'Admin cannot block/unblock themselves.'" => "__('message.admin_cannot_block_self')",
    "'Failed to block group member.'" => "__('message.failed_to_block_group_member')",
    "'Failed to unblock group member.'" => "__('message.failed_to_unblock_group_member')",
    "'Failed to update group member status.'" => "__('message.failed_to_update_group_member_status')",

    "'Group ID and is_member_permission are required.'" => "__('message.group_id_and_permission_required')",
    "'Only group admin can update permissions.'" => "__('message.only_admin_update_permissions')",
    "'Failed to update permissions for all members.'" => "__('message.failed_to_update_permissions_for_all')",

    "'Group ID, User ID and is_member_permission are required.'" => "__('message.group_user_permission_required')",
    "'Failed to update permission for member.'" => "__('message.failed_to_update_member_permission')",

    "'Only group admin can view permissions.'" => "__('message.only_admin_view_permissions')",
    "'Failed to fetch permission for member.'" => "__('message.failed_to_fetch_member_permission')",

    "'You do not have access to this private group.'" => "__('message.no_access_private_group')",

    "'Group messages and members fetched successfully.'" => "__('message.group_messages_members_fetched')",
    "'Failed to fetch group conversation detail.'" => "__('message.failed_to_fetch_group_conversation_detail')",

    "'Only group admin can delete the group.'" => "__('message.only_admin_delete_group')",
    "'Failed to delete group.'" => "__('message.failed_to_delete_group')",

    "'No valid fields provided for update.'" => "__('message.no_valid_fields_for_update')",
    "'Only group admin can edit the group.'" => "__('message.only_admin_edit_group')",
    "'Invalid value for one or more fields. Please check your input.'" => "__('message.invalid_group_field_value')",
    "'Database error occurred while updating group.'" => "__('message.database_error_updating_group')",
    "'Failed to update group.'" => "__('message.failed_to_update_group')",

    "'You are not a member of this group.'" => "__('message.you_are_not_member_of_group')",
    "'Admin cannot leave the group. Please assign another admin before leaving.'" => "__('message.admin_cannot_leave_group')",
    "'Failed to leave group.'" => "__('message.failed_to_leave_group')",

    "'Group ID, reason, and report type are required.'" => "__('message.group_reason_report_type_required')",
    "'You have already reported this group.'" => "__('message.already_reported_group')",
    "'Failed to report group.'" => "__('message.failed_to_report_group')",

    "'User ID, reason, and report type are required.'" => "__('message.user_reason_report_type_required')",
    "'You have already reported this User.'" => "__('message.already_reported_user')",
    "'Failed to report group.'" => "__('message.failed_to_report_group')",
    "'Failed to report pin.'" => "__('message.failed_to_report_pin')",

    "'Pin ID and report type are required.'" => "__('message.pin_id_required')",
    "'You have already reported this Pin.'" => "__('message.already_reported_pin')",

    "'Invalid type parameter.'" => "__('message.invalid_type_parameter')",
    "'Failed to fetch group media list.'" => "__('message.failed_to_fetch_group_media_list')",
    "'Failed to fetch individual media list.'" => "__('message.failed_to_fetch_individual_media_list')",

    "'Failed to fetch latest group messages.'" => "__('message.failed_to_fetch_latest_group_messages')",
    "'Failed to fetch latest individual messages.'" => "__('message.failed_to_fetch_latest_individual_messages')",

    "'Failed to delete group chat for your account.'" => "__('message.failed_to_delete_group_chat_for_user')",
    "'Only group admin can delete all group messages.'" => "__('message.only_admin_delete_all_group_messages')",
    "'Failed to delete all group messages.'" => "__('message.failed_to_delete_all_group_messages')",
];

$originalContent = $content;
$content = str_replace(array_keys($replacements), array_values($replacements), $content);

if ($content === $originalContent) {
    exit("No replacements made. Check file path or message strings.\n");
}

$backupPath = $filePath . '.bak';
file_put_contents($backupPath, $originalContent);
file_put_contents($filePath, $content);

echo "Done.\n";
echo "Backup created: {$backupPath}\n";
echo "Updated file: {$filePath}\n";