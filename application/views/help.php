<div class="tips">
<h1>Tips for using the marking system</h1>
<h2>General</h2>
<ol>
    <li>Make sure you understand in general how the marking system works, as
        explained in the <?php echo anchor("marker/about", "'about' page"); ?>.
    </li>
    <li>While marking, keep an eye on the total mark at the bottom, to make sure
        you're on track.
    </li>
    <li>Keep in mind at all times that your first goal is to come up with a
        mark that <em>feels</em> right for the assignment you're marking. Your
        second goal is to ensure that the marksheet the student sees
        will provide useful feedback and tell them where they've lost
        marks.
    </li>
    <li>Particularly when you're learning to use the system, it's vital that
        you view the marklogs for each student after you've submitted their
        marksheet.
    </li>
    <li>Don't be too literal in your interpretation of the values of
        any existing mark items as specified when the administrator defined the
        assigment. Tweak the weight field as necessary to give you
        an appropriate total.
    </li>
    <li>Remember that you can edit the comment part of any of the mark items
        if the comment isn't exactly right for the student you're marking.
        Editing existing items in this way affects only the marksheet you're
        currently working on.
    </li>
    <li>Remember than you can add your own comment items as you go. The items
        you add belong to you only; other markers don't see them.
    </li>
    <li>When you add a new comment item, make it persistent if you think it's
        at all likely that you'll use it again. You can always 'de-persist' it
        if you later decides it's cluttering your work space. [When I get around
        to implementing this feature, anyway!]
    </li>
    <li>The comments in mark items and the <em>Extra comments</em>
        are all subject to <em>Markdown</em> formatting, so you can display code
        blocks or numbered lists etc. See below for details.
    </li>
</ol>
<h2>Markdown in the Marking system</h2>
<p>
Markdown is a lightweight wiki-style formatting system used on many blog
sites such as stackoverflow.com and github. All comments that you enter into
mark sheet text fields are subject to markdown formatting when the marklog
is generated.
</p>
<p>The full syntax for Markdown formatting is given
    <a href="http://daringfireball.net/projects/markdown/syntax">here</a>; the
    version used on this site conforms to that standard with the important
    exception that underscores are not recognised for emphasis, emboldening etc;
    use the asterisk approach instead (which is also standard). [Underscores
    are widely used within identifiers in program code and their use as
    formatting metacharacters can be confusing.]
</p>
<p>
    Most of the Markdown capabilities aren't relevant within a marksheet, but
    the following can often prove useful:
<ol>
    <li>To display a word or phrase in italics, enclose it in asterisks, e.g. '*this*'
        will display as <em>this</em>.
    </li>
    <li>To display a word or phrase in bold, enclose it in double asterisks, e.g.
        '**this**' will display as <strong>this</strong>.
    </li>
    <li>To get a paragraph break within a comment, use a blank (empty) line.
    </li>
    <li>To display a block of code in standard monospaced pre-formatted style,
        insert a blank line then all the code lines, each indented by at least
        4 spaces, followed by another blank line at the end of the block. For example:
        <p>
        &nbsp;&nbsp;&nbsp;&nbsp;is_bad = True<br />
        &nbsp;&nbsp;&nbsp;&nbsp;if is_bad:<br />
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;print("Oops")
        </p>
        <p>
        will display as</p>
        <pre>is_bad = True
if is_bad:
    print("Oops")
        </pre>
    </li>
    <li>To display a word in a monospaced font within normal text,
        enclose it in backticks, e.g. `word_of_code` will display as
    <tt>word_of_code</tt>.
</li>

</ol>
</p>
<?php include('footer.php'); ?>
</div>