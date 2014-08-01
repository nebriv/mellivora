<?php

require('../include/mellivora.inc.php');

login_session_refresh();

if (!isset($_GET['group']) || strlen($_GET['group']) < 2) {
    message_error('Please supply a valid group code');
}

$group = db_select_one(
    'user_types',
    array(
        'id',
        'title',
        'description'
    ),
    array(
        'title'=>$_GET['group']
    )
);

if (!$group) {
    message_error('No group found with that code');
}

head($group['title']);


    section_head(htmlspecialchars($group['title']), $group['description'], false);

    $scores = db_query_fetch_all('
            SELECT
               u.id AS user_id,
               u.team_name,
               u.competing,
               co.id AS country_id,
               co.country_name,
               co.country_code,
               SUM(c.points) AS score,
               MAX(s.added) AS tiebreaker
            FROM user_types AS ut,users AS u
            LEFT JOIN countries AS co ON co.id = u.country_id
            LEFT JOIN submissions AS s ON u.id = s.user_id AND s.correct = 1
            LEFT JOIN challenges AS c ON c.id = s.challenge
            WHERE u.competing = 1 AND ut.id = :usertype_id AND ut.id = u.user_type
            GROUP BY u.id
            ORDER BY score DESC, tiebreaker ASC',
        array(
            'usertype_id'=>$group['id']
        )

    );
    scoreboard($scores);


foot();
