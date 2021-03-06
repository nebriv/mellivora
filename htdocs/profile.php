<?php

require('../include/mellivora.inc.php');

enforce_authentication();

$user = db_select_one(
    'users',
    array(
        'team_name',
        'email',
        'enabled',
        'competing',
        'country_id',
        '2fa_status',
		'user_type'
    ),
    array('id' => $_SESSION['id'])
);

head('Profile');

section_subhead('Profile settings', '| <a href="user?id='.htmlspecialchars($_SESSION['id']).'">View public profile</a>', false);
echo '<p><i>Contact an administrator to change locked options</i></p>';
form_start('actions/profile');
form_input_text('Email', $user['email'], true);
form_input_text('Team name', $user['team_name'], true);
$teamtype = db_query_fetch_one('SELECT title from user_types where user_types.id = '.$user['user_type']);
form_input_text('Group', $teamtype['title'], true);

$opts = db_query_fetch_all('SELECT * FROM countries ORDER BY country_name ASC');
form_select($opts, 'Country', 'id', $user['country_id'], 'country_name');

form_hidden('action', 'edit');
form_button_submit('Save changes');
form_end();

section_subhead('Two-factor authentication', 'using Google Authenticator');
form_start('actions/profile');
if ($user['2fa_status'] == 'generated') {
    form_generic('QR', '<img src="'.get_two_factor_auth_qr_url().'" alt="QR" title="Scan with Google Authenticator app" />');
    form_input_text('Code');
    form_hidden('action', '2fa_enable');
    form_button_submit('Enable two-factor authentication');
} else if ($user['2fa_status'] == 'disabled') {
    form_hidden('action', '2fa_generate');
    form_button_submit('Generate codes');
} else if ($user['2fa_status'] == 'enabled') {
    form_generic('QR', '<img src="'.get_two_factor_auth_qr_url().'" alt="QR" title="Scan with Google Authenticator app" />');
    form_hidden('action', '2fa_disable');
    form_button_submit('Disable two-factor authentication', 'danger');
}
form_end();

section_subhead('Reset password');
form_start('actions/profile');
form_input_password('Current password');
form_input_password('New password');
form_input_password('New password again');
form_hidden('action', 'reset_password');
form_input_captcha();
form_button_submit('Reset password', 'warning');
form_end();

foot();
