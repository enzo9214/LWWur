Made by: Nadir Latif (nadir1915@yahoo.com)

Dependencies: Uses getID3() by James Heinrich <info@getid3.org> available at http://getid3.sourceforge.net for               extracting id3 tag information.

This script can be used on sites that provide access to thousands of mp3s. The script requires the path to a folder containing multiple mp3s. It will then extract tags including album art, place the tags in database and clip the mp3s.

1) Usage:

-Import the database file that has been included (db_structure.sql).
-Copy the files to the directory of a web server that supports php4 and above.
-Change the variables :$clips_folder,$log_folder and $original_folder in process_files.php. These are the locations of the folders that contain log files,album_art, clips and the original mp3s. Create the folders corresponding to these variables. The variable clip_duration holds the duration of clipped mp3s. These variables are defined at the beginning of the process_files.php file. The database information and the names of all the tables can be changed in config.inc.php.

-To run the script on linux, set the variable $is_linux=true.

-Run the file index.php from a browser. Type in the name of the folder containing the mp3s and select the checkbox if the full path of the mp3 files contains tag information. The press Process Files button. The specified folder can contain thousands of mp3s in various sub folders.

2) What does this script do?

-It gets the name of each file in the specified folder.
-For each music file in every folder and its subfolder, the id3 tags are extracted and saved to database in the mp_id3_tags table.
-If album art is present in the tags it will also be extracted and saved to the album arts folder.
-Twenty second clips of each file is created and stored in the Clips folder (the clips are stored in a subfolder of the same name as the original file). The duration of the clips can easily be changed in process_files.php.
-Debugging information is continuously displayed on screen. The progress made by the script is emailed after every 1000 mp3s have been processed. Any errors encountered by the script are also emailed.
-Summary of all the activities is stored in mp_admin_summary table.
-List of files with empty tags and list of duplicate files is stored in mp_admin_errors table.
-In the log files folder a text file listing the files with empty tags and duplicate files is created. An mp3 is considered to be a duplicate if its id3 tags match the id3 tags of a previously imported mp3. Duplicate mp3s are not imported.
-This script may be used with the "Amazon Album Art Extraction" script and the "Mp3 Tag Correction And Retrieval" script.
-Errors in the script are handled by a custom error handler defined in set_error_handling.php.

3)List of files:

a)config.inc.php (database information and table names)
b)clip_files.php (used to create a clip of an mp3)
c)db_structure.sql (holds the database structure)
d)mysql.php,query_builder.php (provide database functions)
e)process_files.php (main program file)
f)index.php (initial file)
g)readme.txt (help file)
h) porter_stemmer.php (contains the porter stemmer algorithm)(no longer used)
i)the folders helperapps and getid3 are used to extract id3 tag information. Removing any files from these folders will cause problems in tag extraction.
j)set_error_handling.php (used to set the error handler to a user defined function)

-Feel free to contact me for any assistance regarding this script.