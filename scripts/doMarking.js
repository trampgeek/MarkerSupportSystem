// Script for the markers "doMarking" page.
(function () {
    "use strict";

    function floatField(id) {
        return parseFloat($("#" + id).val());
    }

    // Extract an id number from the id attribute of the given element
    function getId(elem) {
        var idPat = /^[a-zA-Z]+([0-9]+?)[a-zA-Z.]*$/, id, match;
        id = elem.attr('id');
        match = idPat.exec(id);
        return match ? match[1] : 0;
    }


    // Compute the total mark for this assignment given all the currently
    // checked checkboxes.
    function computeAll() {
        var totMark, isValid, bonus, startingMark, totPenalty;
        bonus = floatField('bonus');
        startingMark = floatField('startingMark');
        totMark = startingMark + bonus;
        totPenalty = 0.0;
        $('table.markItems tr input.item-checkbox:checked').each(function (i, cb) {
            var id, mark, weight, value, elem;
            elem = $(cb);
            id = getId(elem);
            if (elem.attr('name')[0] === 'x') {
                mark = 0; // This is an extra comment, so has mark of zero
            } else {
                mark = parseFloat($('#mark' + id).html());
            }
            if (mark !== 0) {
                weight = floatField('cb' + id + 'wt');
                value = mark * weight;
                if (mark < 0) {
                    totPenalty -= value;
                } else {
                    totMark += value;
                }
            }
        });

        if (totPenalty > 0) {
            totMark -= totPenalty / floatField('pseudoMaxPenalty');
        }
        $('#markTotal').val(totMark);
    }


    // Check if there are any positive-mark mark items that are
    // unchecked, and (regardless) confirm user wishes to submit.
    function checkSubmission() {
        var uncheckedPositives, result = false;
        computeAll();
        uncheckedPositives = $('table.markItems tr').filter(function (i) {
            return parseInt($('.mark', this).html(), 10) > 0 &&
                $(this).has('input.item-checkbox:not(:checked)').length > 0;
        });
        if (uncheckedPositives.length > 0) {
            result = confirm("WARNING: there are unselected mark items worth marks. " +
                "Do you still wish to submit?");
        } else {
            result = confirm("Submit marksheet");
        }
        return result;
    }


    function comboChanged(evt) {
        var elem = $(evt.target), id = getId(elem);
        $('#cb' + id).attr('checked', true);
        $('#cb' + id + 'wt').val(elem.val());  // Copy combo box into weight
        computeAll();
    }


    function weightChanged(evt) {
        var elem = $(evt.target), id = getId(elem);
        $('#cb' + id).attr('checked', true);
        computeAll();
    }


    function commentChanged(evt) {
        var elem = $(evt.target), id = getId(elem);
        if (elem.attr('class') === 'extraComment') {
            // This is a newly-added comment
            $('#xcb' + id).attr('checked', true);
        } else {
            $('#cb' + id).attr('checked', true);
        }
    }


    function addComment(evt) {
        var catId, table, num, extraCommentCountField, rowHtml;
        catId = getId($(evt.target));
        table = $('#markItems' + catId);
        extraCommentCountField = $('[name="extraCommentCount"]');
        num = parseInt(extraCommentCountField.val(), 10);
        num += 1;
        extraCommentCountField.val(num);

        rowHtml = "<tr class='comment'>" +
            "<td class='description'>" +
            "<textarea class='extraComment' name='extraComment" + num +
            "' width='80' height='1' id='extraComment" + num + "' ></textarea>" +
            "<input type='hidden' name='xccatid" + num + "' value='" + catId + "' /></td>" +
            "<td><input type='checkbox' class='item-checkbox' id='xcb" + num +
            "' name='xcb" + num + "' /></td>" +
            "<td class='mark'>0</td><td><input type='checkbox' name='persist" + num +
            "' /> Persist</td></tr>";
        table.append(rowHtml);
        $('#extraComment' + num).keyup(commentChanged);
        $('#extraComment' + num).autosize();
    }


    function removeComment(evt) {
        var catId, lastRow;
        catId = getId($(evt.target));
        lastRow = $('#markItems' + catId + ' tr:last').filter(function (i) {
            return $('textarea', this).val() == ''; // Must be empty
        });
        if (lastRow.length === 1) {
            lastRow.remove();
        }
    }


    $(document).ready(function () {
        $('.markcombo').change(comboChanged);
        $('.item-checkbox,#bonus,.weight').change(computeAll);
        $('.weight').change(weightChanged);
        $('textarea.comment').keyup(commentChanged);
        $('#markingForm').submit(checkSubmission);
        $('.add-comment-button').click(addComment);
        $('.remove-comment-button').click(removeComment);
        $('textarea').autosize();
        computeAll();
    });
}());
