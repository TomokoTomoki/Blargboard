<?php

// BBCode parser core.
// Parses BBCode and HTML intelligently so the output is reasonably well-formed, and doesn't contain evil stuff.

// TODO list
// * check that some tags only appear where they're allowed (ie <td> only in <tr>)

define('TAG_GOOD', 			0x0001);	// valid tag

define('TAG_BLOCK', 		0x0002);	// block tag (subject to newline removal after start/end tags)
define('TAG_SELFCLOSING',	0x0004);	// self-closing (br, img, ...)
define('TAG_CLOSEOPTIONAL',	0x0008);	// closing tag optional (tr, td, li, p, ...)
define('TAG_RAWCONTENTS',	0x0010);	// tag whose contents shouldn't be parsed (<style>, [code], etc)
define('TAG_NOAUTOLINK',	0x0020);	// prevent autolinking 

$HTMLTagList = array
(
	'a'			=>	TAG_GOOD | TAG_NOAUTOLINK,
	'b'			=>	TAG_GOOD,
	'big'		=>	TAG_GOOD,
	'br'		=>	TAG_GOOD | TAG_SELFCLOSING,
	'caption'	=>	TAG_GOOD | TAG_CLOSEOPTIONAL,
	'center'	=>	TAG_GOOD,
	'code'		=>	TAG_GOOD,
	'dd'		=>	TAG_GOOD,
	'del'		=>	TAG_GOOD,
	'div'		=>	TAG_GOOD | TAG_BLOCK,
	'dl'		=>	TAG_GOOD,
	'dt'		=>	TAG_GOOD,
	'em'		=>	TAG_GOOD,
	'font'		=>	TAG_GOOD,
	'h1'		=>	TAG_GOOD | TAG_BLOCK,
	'h2'		=>	TAG_GOOD | TAG_BLOCK,
	'h3'		=>	TAG_GOOD | TAG_BLOCK,
	'h4'		=>	TAG_GOOD | TAG_BLOCK,
	'h5'		=>	TAG_GOOD | TAG_BLOCK,
	'h6'		=>	TAG_GOOD | TAG_BLOCK,
	'hr'		=>	TAG_GOOD | TAG_SELFCLOSING,
	'i'			=>	TAG_GOOD,
	'img'		=>	TAG_GOOD | TAG_SELFCLOSING,
	'input'		=>	TAG_GOOD | TAG_SELFCLOSING,
	'kbd'		=>	TAG_GOOD,
	'li'		=>	TAG_GOOD | TAG_CLOSEOPTIONAL,
	'nobr'		=>	TAG_GOOD,
	'ol'		=>	TAG_GOOD,
	'p'			=>	TAG_GOOD | TAG_BLOCK | TAG_CLOSEOPTIONAL,
	'pre'		=>	TAG_GOOD | TAG_RAWCONTENTS,
	's'			=>	TAG_GOOD,
	'small'		=>	TAG_GOOD,
	'span'		=>	TAG_GOOD,
	'strong'	=>	TAG_GOOD,
	'style'		=>	TAG_GOOD | TAG_BLOCK | TAG_RAWCONTENTS,
	'sub'		=>	TAG_GOOD,
	'sup'		=>	TAG_GOOD,
	'table'		=>	TAG_GOOD | TAG_BLOCK,
	'tbody'		=>	TAG_GOOD | TAG_BLOCK | TAG_CLOSEOPTIONAL,
	'td'		=>	TAG_GOOD | TAG_BLOCK | TAG_CLOSEOPTIONAL,
	'textarea'	=>	TAG_GOOD | TAG_BLOCK | TAG_RAWCONTENTS,
	'tfoot'		=>	TAG_GOOD | TAG_BLOCK | TAG_CLOSEOPTIONAL,
	'th'		=>	TAG_GOOD | TAG_BLOCK | TAG_CLOSEOPTIONAL,
	'thead'		=>	TAG_GOOD | TAG_BLOCK | TAG_CLOSEOPTIONAL,
	'tr'		=>	TAG_GOOD | TAG_BLOCK | TAG_CLOSEOPTIONAL,
	'u'			=>	TAG_GOOD,
	'ul'		=>	TAG_GOOD,
	'link'		=>	TAG_GOOD | TAG_BLOCK | TAG_SELFCLOSING,
	
	'audio'		=>	TAG_GOOD,
);

$BBCodeTagList = array
(
	'b'			=>	TAG_GOOD,
	'i'			=>	TAG_GOOD,
	'u'			=>	TAG_GOOD,
	's'			=>	TAG_GOOD,
	
	'url'		=>	TAG_GOOD | TAG_NOAUTOLINK,
	'img'		=>	TAG_GOOD | TAG_NOAUTOLINK,
	'imgs'		=>	TAG_GOOD | TAG_NOAUTOLINK,
	
	'user'		=>	TAG_GOOD | TAG_SELFCLOSING,
	'thread'	=>	TAG_GOOD | TAG_SELFCLOSING,
	'forum'		=>	TAG_GOOD | TAG_SELFCLOSING,
	
	'quote'		=>	TAG_GOOD | TAG_BLOCK,
	'reply'		=>	TAG_GOOD | TAG_BLOCK,
	
	'spoiler' 	=>	TAG_GOOD | TAG_BLOCK,
	'code'		=>	TAG_GOOD | TAG_BLOCK | TAG_RAWCONTENTS,
	
	'table'		=> 	TAG_GOOD | TAG_BLOCK,
	'tr'		=> 	TAG_GOOD | TAG_BLOCK | TAG_CLOSEOPTIONAL,
	'trh'		=> 	TAG_GOOD | TAG_BLOCK | TAG_CLOSEOPTIONAL,
	'td'		=> 	TAG_GOOD | TAG_BLOCK | TAG_CLOSEOPTIONAL,
	
	'youtube' 	=> 	TAG_GOOD | TAG_NOAUTOLINK,
);

$TagLists = array('<' => $HTMLTagList, '[' => $BBCodeTagList);


function filterTag($tag, $attribs, $contents, $close)
{
	global $HTMLTagList, $bbcodeCallbacks;
	
	$tagname = substr($tag,1);
	
	if ($tag[0] == '<')
	{
		$output = $tag.$attribs.$contents;
		// TODO filter attributes? (remove onclick etc)
		// this is done by the security filter, though, so it'd be redundant
		
		if ($close || !($HTMLTagList[$tagname] & (TAG_CLOSEOPTIONAL | TAG_SELFCLOSING)))
			$output .= '</'.$tagname.'>';
	}
	else
	{
		$attribs = substr($attribs,1,-1);
		$output = $bbcodeCallbacks[$tagname]($contents, $attribs);
	}
	
	return $output;
}

function filterText($s, $parentTag, $parentMask)
{
	if ($parentMask & TAG_RAWCONTENTS) return $s;
	$s = nl2br($s);
	$s = postDoReplaceText($s, $parentTag, $parentMask);
	return $s;
}


function parseBBCode($text)
{
	global $tokens, $tokenPtr, $parseStatus, $tokenCt;
	global $TagLists;
	$spacechars = array(' ', "\t", "\r", "\n");
	
	$raw = preg_split('@([\[<]/?[a-zA-Z][a-zA-Z0-9]*)@', $text, 0, PREG_SPLIT_DELIM_CAPTURE);
	$outputstack = array(0 => array('tag' => '', 'attribs' => '', 'contents' => ''));
	$si = 0;
	
	$currenttag = '';
	$currentmask = 0;
	
	$i = 0; $nraw = count($raw);
	while ($i < $nraw)
	{
		$cur = $raw[$i++];
		if ($cur[0] == '<' || $cur[0] == '[') // we got a tag start-- find out where it ends
		{
			$isclosing = $cur[1] == '/';
			$tagname = substr($cur, ($isclosing ? 2:1));
			$taglist = $TagLists[$cur[0]];
			$closechar = ($cur[0] == '<') ? '>' : ']';
			
			// raw contents tags (<style> & co)
			// continue outputting RAW content until we meet a matching closing tag
			if (($currentmask & TAG_RAWCONTENTS) && (!$isclosing || $currenttag != $cur[0].$tagname))
			{
				$outputstack[$si]['contents'] .= $cur;
				continue;
			}
			
			// invalid tag -- output it escaped
			if (!array_key_exists($tagname, $taglist))
			{
				$outputstack[$si]['contents'] .= filterText(htmlspecialchars($cur), $currenttag, $currentmask);
				continue;
			}
			
			// we got a proper tag? find where it ends
			$tagmask = $taglist[$tagname];
			
			$next = $raw[$i++];
			
			$j = 0;
			$endfound = false;
			$inquote = false; $inattrib = false;
			for (;;)
			{
				$nlen = strlen($next);
				for (; $j < $nlen; $j++)
				{
					$ch = $next[$j];
					
					if (!$inquote)
					{
						if ($ch == $closechar)
						{
							$endfound = true;
							break;
						}
						
						if ($ch == '=')
							$inattrib = true;
						else if ($inattrib)
						{
							if (in_array($ch, $spacechars))
								continue;
							
							if ($ch == '"' || $ch == '\'')
								$inquote = $ch;
							else
								$inquote = ' ';
						}
					}
					else if ($ch == $inquote || 
						($inquote == ' ' && in_array($ch, $spacechars)))
					{
						$inquote = false;
						$inattrib = false;
					}
					else if ($inquote == ' ' && $ch == $closechar)
					{
						$endfound = true;
						break;
					}
				}
				
				if ($endfound)
					break;
				
				if ($i >= $nraw)
					break;
				
				if ($j >= $nlen)
					$next .= $raw[$i++];
				else
					break;
			}
			
			if (!$endfound) // tag end not found-- call it invalid
				$outputstack[$si]['contents'] .= filterText(htmlspecialchars($cur.$next), $currenttag, $currentmask);
			else
			{
				$tagattribs = substr($next,0,$j+1);
				$followingtext = substr($next,$j+1);
				
				if ($tagmask & TAG_BLOCK)
					$followingtext = preg_replace("@^\r?\n@", '', $followingtext);
				
				if ($isclosing)
				{
					$tgood = false;
					
					// tag closing. Close any tags that need it before.
					
					$k = $si;
					while ($k > 0)
					{
						$closer = $outputstack[$k--];
						if ($closer['tag'] == $cur[0].$tagname)
						{
							$tgood = true;
							break;
						}
					}
					
					if ($tgood)
					{
						while ($si > 0)
						{
							$closer = $outputstack[$si--];
							$ccontents = $closer['contents'];
							$cattribs = $closer['attribs'];
							$ctag = $closer['tag'];
							$ctagname = substr($ctag,1);
							
							if ($ctag != $cur[0].$tagname)
								$outputstack[$si]['contents'] .= filterTag($ctag, $cattribs, $ccontents, false);
							else
								break;
						}
						
						$currenttag = $outputstack[$si]['tag'];
						$currentmask = $TagLists[$currenttag[0]][substr($currenttag,1)];
						
						$outputstack[$si]['contents'] .= filterTag($ctag, $cattribs, $ccontents, true).filterText($followingtext, $currenttag, $currentmask);
					}
					else
						$outputstack[$si]['contents'] .= filterText(htmlspecialchars($followingtext), $currenttag, $currentmask);
				}
				else if ($tagmask & TAG_SELFCLOSING)
				{
					$followingtext = filterText($followingtext, $currenttag, $currentmask);
					$outputstack[$si]['contents'] .= filterTag($cur, $tagattribs, '', false).$followingtext;
				}
				else
				{
					$followingtext = filterText($followingtext, $cur, $tagmask);
					
					if (($currentmask & TAG_CLOSEOPTIONAL) && $currenttag == $cur)
					{
						$closer = $outputstack[$si--];
						$ccontents = $closer['contents'];
						$cattribs = $closer['attribs'];
						$ctag = $closer['tag'];
						$ctagname = substr($ctag,1);
						
						if (!($TagLists[$ctag[0]][$ctagname] & TAG_SELFCLOSING))
							$outputstack[$si]['contents'] .= filterTag($ctag, $cattribs, $ccontents, false);
					}
						
					$outputstack[++$si] = array('tag' => $cur, 'attribs' => $tagattribs, 'contents' => $followingtext);
					
					$currenttag = $cur;
					$currentmask = $tagmask;
				}
			}
		}
		else if ($cur)
			$outputstack[$si]['contents'] .= filterText($cur, $currenttag, $currentmask);
	}
	
	// close any leftover opened tags
	while ($si > 0)
	{
		$closer = $outputstack[$si--];
		$ccontents = $closer['contents'];
		$cattribs = $closer['attribs'];
		$ctag = $closer['tag'];
		$ctagname = substr($ctag,1);
		
		if (!($TagLists[$ctag[0]][$ctagname] & TAG_SELFCLOSING))
			$outputstack[$si]['contents'] .= filterTag($ctag, $cattribs, $ccontents, false);
	}

	return $outputstack[$si]['contents'];
}

?>
