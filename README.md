About Quizable
--------------

As busy teachers, we needed a quick way of putting online, deadlined,
and auto-graded questions in front of our students. We think that posing
2-3 (deadlined) questions to students soon after a class ends is a great
way for them to assess and absorb the current topic in a timely manner.
This may even encourage them to study, read, and keep up with course
material. As one of our students said, "the questions helped me to keep
my head into the class." We've used the usual online course management
systems (Blackboard, Moodle, etc.), and they simply require too many
clicks to post even a single question. We wanted a system that allowed
us to compose and issue questions with about the same effort it takes to
send an email--a little typing, followed by clicking "go." Welcome to
Quizable.

For example, to post a multiple choice question, worth 5 points,
allowing the students 3 tries with a 0.5 point deduction per try, we'd
type:

mc//Which planet is closest to the
sun?//Earth//Mars//*Mercury//Jupiter//#end//5//3//0.1//yes Here "mc"
stands for multiple choice, and the "yes" at the end, tells the system
to display the correct answer after the deadline has passed. The star
tags "Mercury" as the correct answer. We also have MathJax built right
in, so properly typesetting mathmematics in our questions is easy as
$5x^2+2x+6$ or $\int_0^1 f(x)dx$. You can also include figures with your
questions, if some kind of an illustration is needed.

We put a tutorial of Quizable on the site at Quizable.org (see the links
in the upper right corner).

Technical
---------
Quizable was written in PHP using CodeIgniter. The database it needs
is MySQL.  See the application/config folder and tweak config.php
and database.php to get things running.  The file called init
in the /database folder should be used to created the needed
database tables.
