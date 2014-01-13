	<table class="outline margin threadlist">
		<tr class="header1">
			<th>Threads</th>
		</tr>
		{foreach $threads as $thread}
		{if $dostickies && !$thread@first && $laststicky != $thread.sticky}
		<tr class="header1"><th style="height:5px;"></th></tr>
		{/if}
		{$laststicky=$thread.sticky}
		<tr class="cell{if $dostickies && $thread.sticky}2{elseif $thread@index is odd}1{else}0{/if}">
			<td>
				{$thread.new}
				{$thread.poll}
				{$thread.link}
				{if $thread.pagelinks} <small>[{$thread.pagelinks}]</small>{/if}
				<br>
				<small>By {$thread.startuser}
				{if $showforum} in {$thread.forumlink}{/if}
				&mdash; {$thread.replies} {if $thread.replies==1}reply{else}replies{/if}<br>
				<a href="{$thread.lastpostlink}">Last post</a> by {$thread.lastpostuser} on {$thread.lastpostdate}</small>
			</td>
		</tr>
		{/foreach}
	</table>
