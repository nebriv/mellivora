<?php

require('../include/mellivora.inc.php');

login_session_refresh();

head('Home');

if (cache_start('home', CONFIG_CACHE_TIME_HOME)) {

    require(CONFIG_PATH_THIRDPARTY . 'nbbc/nbbc.php');

    $bbc = new BBCode();
    $bbc->SetEnableSmileys(false);
	section_head("Capture the Flag");

	echo '<div class="jumbotron"><p>Second Official Champlain College CNIS Club Capture the Flag Competition is opening 9/13/14 at 8:00PM! Register now! <br /> Teams of up to 4 people, or work by yourself. The competition will close on 9/15/14 at 8:00PM, prizes are to be determined based on finances.</p></div>';

	echo '<div class="col-lg-6">';
	section_subhead("What is a CTF?");
	echo '<p>CTF stands for Capture the Flag, and is generally a competition between teams or individuals trying to find flags on systems or files provided by the competition hosts. There are two different styles, jeopardy and attack/defend, the CNIS CTF will be jeopardy style.</p>
          <p><a class="btn" href="http://en.wikipedia.org/wiki/Capture_the_flag#Computer_security">More Information &raquo;</a></p>';
	echo '</div>';
	
	echo '<div class="col-lg-6">';
	section_subhead("What is the CNIS Club?");
	echo '
        <p>It\'s a club for the Computer Networking and Information Security (CNIS) major at Champlain College. This club is open to all students from all majors.  We explore various networking and security topics, work on projects and learn about new technologies in the field. </p>
		<p><a href="cnisclub.org" class="btn">Visit the site &raquo;</a></p>';
	echo '</div>';

    $news = db_query_fetch_all('SELECT * FROM news ORDER BY added DESC');
	if ($news) {
	echo '<center>';
	section_head("Updates");
	echo '</center>';
    }
	foreach ($news as $item) {
        echo '
        <div class="news-container">';
            section_subhead($item['title']);
            echo '
            <div class="news-body">
                ',$bbc->parse($item['body']),'
            </div>
        </div>
        ';
    }

    cache_end('home');
}

foot();
