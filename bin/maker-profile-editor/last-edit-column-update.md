# Maker Profile Editor: New "Last Edit" Tracking

**Date:** 2026-08-05

## What I added

I added a new "Last Edit" column to the Makers list in the admin dashboard. It shows the last time a Maker actually made a change through their Profile Editor, along with a friendly "X days ago" note underneath.

You can also click the column header to sort the whole list by it, so it's easy to see who's been keeping their profile up to date and who hasn't touched it in a while.

## Why I added it

A few weeks back, Kelsi asked a great question on Basecamp: is the "Last Modified" date under the Date column actually showing when Makers edit their pages? She'd noticed a lot of profiles marked "published," but no real way to tell how or when people were editing them, and she suspected fewer Makers were actively updating their pages than we'd like.

That question turned out to be spot on. That "Last Modified" date changes for all kinds of reasons that have nothing to do with a Maker editing anything themselves. It updates when I make an edit on the backend, when we run a bulk import, or anytime our system touches that listing behind the scenes. So a profile could show a "recent" date even if the Maker hasn't logged in for a year.

The new "Last Edit" column only updates in one specific case: when a Maker submits changes through their own Profile Editor. Nothing else touches it. That makes it a much more honest answer to "is this person actually using their profile?"

## One thing to know

This tracking only just started, so it doesn't have any history to draw on yet. Every Maker will show "Never recorded" in that column until the next time they edit their profile, even if they've edited it plenty of times in the past. There's no way for me to go back and recover that older activity, since the old "Date" column was never a reliable stand-in for it either.

Going forward, though, this should give us a genuinely accurate picture of Maker engagement with their profiles.
