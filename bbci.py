

##########################################
##########################################
##
##			-- 		Origin 		--
##	--	Date: 07th of July 2015
##	--	Last Modified: 
## 	--	Ownership: Mark Shine
##	--	Website: www.seferon.com
##########################################
##########################################
##
##			--	Intented Use 	--
##	-- Development Directly for main use
##	.. by App42
##
#########################################
#########################################
##
##		--	 How to get started		--
##	To run this script you need to 
##	install the librarys used below
##
##	To install these you need to use pip
##	The MySQLdb  library requires 
## 	the mySQLDev package installed before
##	$# apt-get install python-dev libmysqlclient-dev
##  $# pip install MySQL-python
##
########################################
########################################


import requests
import BeautifulSoup
import time
import string
import MySQLdb
import sys

#make a array of letters and loop through each letter.
alphabet = list(string.ascii_lowercase)
print alphabet[1]
session = requests.session()
for i in range(len(alphabet)):
	print i
	print alphabet[i]
	req = session.get('http://www.bbc.co.uk/food/ingredients/by/letter/' + alphabet[i] )

	doc = BeautifulSoup.BeautifulSoup(req.content)

	for div in doc.findAll('ol', { "class" : "resources foods grid-view" }):
		for name in div.findAll('a'):
			localName = name.getText()
			if not localName.startswith('Related'):
				print "\n"
				print name.getText()
				mydb = MySQLdb.connect(host = 'SomeMySQLDB', user = 'app', passwd = 'OiStopISeeYouLooking', db = 'foodApp')
				cursor = mydb.cursor()

				cursor.execute("""INSERT INTO ingredients (name) VALUES (%s)""", (name.getText(),))
				mydb.commit()
				mydb.close()		

	time.sleep(30)
