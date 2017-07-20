# InputScanner
A tool designed to scrape a list of URLs and scrape input names (id if no name is found). This tool will also scrape .js urls found on each page (for further testing). This tool was presented at the Bugcrowd LevelUp conference, and the talk can be found here: https://www.youtube.com/watch?v=BEaMhs9LmoY&feature=youtu.be&list=PLIK9nm3mu-S5InvR-myOS7hnae8w4EPFV.

# What's needed
- Somewhere to run PHP. I recommend running XAMPP locally so you can just run the PHP from your computer locally. You can grab XAMPP from here: https://www.apachefriends.org/index.html
- Some PHP knowledge if you wish to modify the script
- BURP Pro license is ideal, but not needed.

# How To Use
Since this tool is built in PHP and I like visual views of my tools, you will need to host this somewhere. I recommend running XAMPP locally so you can just run the PHP from your computer locally. You can grab XAMPP from here: https://www.apachefriends.org/index.html

Now you're setup, it's time to gather some URLs to test. I recommend using a tool like Burp Suite by PortSwigger and using the spider tool on the host you wish to test. Once the spider has finished (or you stop it), right click on the host and click "Copy Urls in this host" (as seen below). 

![Example](https://i.imgur.com/iStPcLw.png "Copy urls")

Once copied, paste them into urls.txt. Now open payloads.txt and enter some payloads you wish to inject into each parameter (such as xss" xss' to test for the reflection of " and ' characters on iputs. This will help automate looking for XSS). This script will inject each payload into each parameter.. so the more payloads, the more requests you'll be sending. 

Now visit http://127.0.0.1/InputScanner/ and you should be presented with this:

![Example](https://i.imgur.com/yAvFy18.png "Copy urls")

Click "Begin Scanner" and it will visit each URL and scrape input names, as well as .js urls found.

Once the scanner is complete you will be given 4 txt file outputs (see below). Use the BURP Intruder to import your lists and run through them (more info on this below). Here is an example of running the GET-output.txt file using BURP intruder (sniper attack type). 

![Example](https://i.imgur.com/rOiLZrU.png "Copy urls")


# Outputs?

4 files are outputted in the /outputs/ folder: JS-output.txt, GET-output.txt, POSTHost-output.txt, POSTData-output.txt.

- GET-output.txt is a file which can be easily imported into a BURP intruder attack (using the Spider type). Set the position in the header (GET §val§ HTTP/1.0) and run the attack. Make sure to play with settings and untick "URL-encode these characters", found on the Payloads tab. Currently the script will echo the HOST url, and I just mass-replace in a text editor such as Sublime. (Replace to null). You are free to modify the script for how you see fit.
- JS-output.xt contains a list of .js urls found on each page. The format is found@https://www.example.com/|https://www.example.com/eg.js|, and this is so you can easily load it into JS-Scan (another tool released by me) and it will let you know where each .js file was found as it scrapes. 
- POSTHost-output.txt contains a list of HOST urls (such as https://www.google.com/) which is used for the "Pitchfork" burp intruder attack. Use this file along with POSTData-output.txt. Set attack type to "Pitch fork" and set one position in the header (same as Sniper attack above), and another at the bottom of the request (the post data sent). Make sure to set a Content-Length etc.
- POSTData-output.txt contains post data. (param1=xss"&param2=xss"&param3=xss")

# Modifying the script
Feel free to modify how you see fit. Some code is sloppy in areas I know. If you need to be authenticated for scraping urls, or you need certain headers, you can modify the file_get_html function found in file-dom.php.

I have error_reporting set to (0), the default_socket_timeout set to 5 and max_execution_time set to 900. Modify these how you see fit (it works fine on my setup).

# Problems/Improvements
If you have any problems you can reach me on twitter as @zseano. 

# Final remarks
This tool may generate noise. Use wisely. I am not responsible for how you use this tool!

This script uses SimpleHTMLDom. Authors and more information can be found in the file-dom.php file.
