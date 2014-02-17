Blargboard

http://kuribo64.net/blargboard/

-------------------------------------------------------------------------------

Board software written in PHP. Uses MySQL for storage.

This is the software that powers Kuribo64 (http://kuribo64.net/). Or well, not quite.
The code provided here is a cleaned up version, with all the K64-specific stuff removed.

It is based off ABXD. ABXD is made by Dirbaio, Nina, GlitchMr & co, and was originally
Kawa's project. See http://abxd.dirbaio.net/ for more details.

It uses Smarty for its templates, and Font Awesome. And possibly some other funny things 
I forgot about.

-------------------------------------------------------------------------------

How to install and use

First of all, if you don't have some PHP and MySQL knowledge, go find something easier
to use, like official ABXD, MyBB or FluxBB. Or phpBB if you're insane.

Get a webserver. Upload the Blargboard codebase to it.

Create a MySQL database and import the provided database.sql file into it.

On your webserver, open config/database.php and fill it with the right info. Open
config/salt.php and config/kurikey.php and edit them as instructed.

Browse to your freshly installed board and register.

First user to register gets full access. For this reason, avoid disclosing the board URL
before you are done installing it.

-------------------------------------------------------------------------------

Features

* Flexible permission system
* Plugin system
* Templates (in the works, about 75% done)
* URL rewriting, enables human-readable forum and thread URLs for public content (requires code editing to enable it as of now)
* Post layouts
* typical messageboard features

-------------------------------------------------------------------------------

Coders and such, who like to hack new features in their software, may think that the use
of templates in Blargboard gets in their way. Well uh, can't please everybody. I tried to
do my best at separating logic and presentation. Besides, the use of templates actually
makes the code nicer. Just look at the first few revisions and see how much duplicate logic
is powering the mobile layout, for example. Templates allowed to get rid of all that madness.

As of now, there are no official releases for this, and the ABXD database installer hasn't
been adapted to Blargboard's database structure yet. Thus, when updating your Blargboard
copy, you need to check for changes to database.sql and modify your database's structure
accordingly.

-------------------------------------------------------------------------------

Board owner's tips

http://board.example/?page=makelr -> browse there after adding categories/forums. Regenerates
the L/R tree used for subforums and such.

-------------------------------------------------------------------------------

TODO list

(no particular order there)

 * port the ABXD database installer/updater (all we need to do is define the database structure, actually)
 * finish implementing templates
 * fix the forum editor
 * improve the permission editing interfaces
 * port the 'show/hide sidebar' feature from Kuribo64? or just nuke the sidebar?
 * allow plugins to add/override templates
 * merge/split threads a la phpBB (albeit without the shitty interface)
 * support multiple password hashing methods? (for importing from other board softwares, or for those who feel SHA256 with per-user salt isn't enough) (kinda addressed via login plugins)
 * more TODO at Kuribo64 and RVLution
 
 * low priority: change/remove file headers? most of the original files still say 'AcmlmBoard XD'

-------------------------------------------------------------------------------

Blargboard is provided as-is, with no guarantee that it'll be useful or even work. I'm not
responsible if it explodes in your face. Use that thing at your own risk.

Oh well, it should work rather well. See Kuribo64. But uh, we never know.

-------------------------------------------------------------------------------

Have fun.

blarg
