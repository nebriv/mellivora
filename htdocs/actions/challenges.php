<?php

require('../../include/mellivora.inc.php');

enforce_authentication();

$time = time();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    validate_xsrf_token($_POST['xsrf_token']);

    if (CONFIG_RECAPTCHA_ENABLE_PRIVATE) {
        validate_captcha();
    }

    if ($_POST['action'] == 'submit_flag') {

        validate_id($_POST['challenge']);

        if (empty($_POST['flag'])) {
            message_error('Did you really mean to submit an empty flag?');
        }

        $submissions = db_select_all(
            'submissions',
            array(
                'correct',
                'added'
            ),
            array(
                'user_id' => $_SESSION['id'],
                'challenge' => $_POST['challenge']
            )
        );

        // make sure user isn't "accidentally" submitting a correct flag twice
        $latest_submission_attempt = 0;
        $num_attempts = 0;
        foreach ($submissions as $submission) {
            $latest_submission_attempt = max($submission['added'], $latest_submission_attempt);

            if ($submission['correct']) {
                message_error('You may only submit a correct flag once.');
            }

            $num_attempts++;
        }

        // get challenge information
        $challenge = db_select_one(
            'challenges',
            array(
                'flag',
                'case_insensitive',
                'automark',
                'available_from',
                'available_until',
                'num_attempts_allowed',
                'min_seconds_between_submissions'
            ),
            array(
                'id' => $_POST['challenge']
            )
        );

        $seconds_since_submission = $time-$latest_submission_attempt;
        if ($seconds_since_submission < $challenge['min_seconds_between_submissions']) {
            message_generic('Sorry','You may not submit another solution for this challenge for another ' . seconds_to_pretty_time($challenge['min_seconds_between_submissions']-$seconds_since_submission));
        }

        if ($num_attempts >= $challenge['num_attempts_allowed']) {
            message_generic('Sorry','You\'ve already tried '.$challenge['num_attempts_allowed'].' times. Sorry!');
        }

        if ($challenge['available_from'] && $time < $challenge['available_from']) {
            message_generic('Sorry','This challenge hasn\'t started yet.');
        }

        if ($challenge['available_until'] && $time > $challenge['available_until']) {
            message_generic('Sorry','This challenge has expired.');
        }

        $correct = false;

        // automark the submission
        if ($challenge['automark']) {

            // lots of people submit with trailing whitespace..
            // we probably never want automarked keys with whitespace
            // at beginning or end, so trimming is probably fine.
            $_POST['flag'] = trim($_POST['flag']);
            $challenge['flag'] = trim($challenge['flag']);

            if ($challenge['case_insensitive']) {
                if (strcasecmp($_POST['flag'], $challenge['flag']) == 0) {
                    $correct = true;
                }
            } else {
                if (strcmp($_POST['flag'], $challenge['flag']) == 0) {
                    $correct = true;
                }
            }
        }

        db_insert(
            'submissions',
            array(
                'added'=>$time,
                'challenge'=>$_POST['challenge'],
                'user_id'=>$_SESSION['id'],
                'flag'=>$_POST['flag'],
                'correct'=>($correct ? '1' : '0'),
                'marked'=>($challenge['automark'] ? '1' : '0')
            )
        );

        if (!$challenge['automark']) {
            redirect('challenges?status=manual');
        }

        redirect('challenges?status=' . ($correct ? 'correct' : 'incorrect'));
    }
}