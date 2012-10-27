<div class='about'>
<h1>A web-based marker-support System</h1>
<h2>Goals</h2>
<p>
This system aims to simplify the organisation and management of assignment
marking of large classes, where there is more than one marker. It assumes that
markers have a checklist of items they are required to check in a student's
submission; it displays that checklist as a web page and allows markers to
tick checkboxes and optionally assign partial marks to each checked item.
A total mark is computed from all the checked/unchecked items and a marklog
generated -- students can view that marklog through the web interface.</p>
<h2>The marking model</h2>
<p>The system is intended to handle both a positive (reward-based)
marking system and a negative (penalty-based) marking system; a mix
of the two is possible but not really recommended because of potential confusion
about the weight of each checklist item.</p>
<p>
In a positive marking system the total mark is broken down into a
set of mark items, each displayed with a checkbox on the marksheet.
The marker ticks those items that are
satisfied by the student's submission, with the possibility of
adjusting the weight value (a number from 0 to 1) by means of an
extra text box. The student's mark is the sum of the products of the
weight factors on each checked item and the marks awarded for that
item.
</p><p>
In a penalty based marking system, the total mark starts off at
some fixed value (representing 100%) and is decremented by a preset
amount for each checked penalty item. With this system there might
be a very large set of possible penalty items so there is an
additional configuration parameter called "pseudoMaxPenalty" that represents
the maximum number of penalty marks that the worst imaginable student might
acquire -- such a student would finish up with zero marks. Essentially it
provides a weighting factor on all penalties.
</p><p>
MarkItems with negative, zero and positive marks are called penalties, comments
and rewards respectively. You should decide whether you're running a reward system,
in which case you use rewards and comments, or a penalty system, in which
case you use penalties and comments. Mixing rewards and penalties
within the same system is possible but not recommended because of potential
confusion about the meaning of the mark assigned to the item: penalties
are weighted by (1/pseudoMaxPenalty) but rewards are not.
</p><p>
The total computed mark is given by
<pre>
mark = startingMark + sum(reward[i] * weight[i]) -
            sum(penalty[i] * weight[i]) / pseudoMaxPenalty
</pre>
</p><p>
A pure "style factor" version is obtained by setting the starting mark
to 1, using only penalties with typical mark values of -1 and
setting pseudoMaxPenalty to a suitably large value so that most
students finish up with a mark in excess of 0.5. Care should be taken
to ensure that no students finish up on negative marks! [Yes, the code could
check for this and make such marks zero but I prefer to leave the responsibility
in the hands of the
administrator because something has probably gone wrong with the marking or
the marking schedule if negative marks occur.] Comments may also be used,
e.g. for positive-sounding remarks.
<h2>Implementation: the three controllers</h2>
<p>
The system is written using the <a href="http://codeigniter.com">
Codeigniter MVC framework</a>. There are three different controllers,
corresponding to the three classes of user: <ol>
<li>The
student controller provides just <em>login</em> and <em>display</em> methods
allowing students simply to view their mark logs. </li>
<li>The marker controller is
used by the team of markers. A marker, whose username must have been set
up by the administrator (see below) for a particular course, logs in using
their usual UoC username and password, but also needs to select the
assignment they're marking. THe system then allows them to select a student from the
class and fill out a marksheet for that student. Markers can also view marklogs
for any or all students -- it is hoped that allowing markers to view other
markers' work will improve the consistency and quality of the marking.
</li><li>The administrator
interface is for use by administrators (who must currently be explicitly
configured in the <em>administrators</em> table). It allows them to set up
assignments, configure the checklist of mark items and their
weights and the various assignment parameters (startingMark, pseudoMaxPenalty).
Administrators can also add/remove markers.</li></ol>
</p><p>
As usual with Code Igniter, the URL contains the controller name, so login URLs are
<ul>
<li>Administrators: <span class='link'>somewhere.canterbury.ac.nz/markingroot/admin</span></li>
<li>Markers: <span class='link'>somewhere.canterbury.ac.nz/markingroot/marker</span></li>
<li>Students: <span class='link'>somewhere.canterbury.ac.nz/markingroot/student</span></li>
</ul>
However, 'student' is the default controller, so students need only be given the simpler URL of
<span class='link'>somewhere.canterbury.ac.nz/markingroot</span> for viewing their mark logs.
</p>
<?php include('footer.php'); ?>
</div>
