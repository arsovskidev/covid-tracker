# Covid Tracker

#### Tracking the impact of COVID-19 in the world.

![](https://img.shields.io/badge/html5%20-%23323330.svg?&style=for-the-badge&logo=html5&logoColor=%23E34F26) ![](https://img.shields.io/badge/css3%20-%23323330.svg?&style=for-the-badge&logo=css3&logoColor=%231572B6) ![](https://img.shields.io/badge/javascript%20-%23323330.svg?&style=for-the-badge&logo=javascript&logoColor=%23F7DF1E) ![](https://img.shields.io/badge/php-%23323330.svg?&style=for-the-badge&logo=php&logoColor=%23777BB4) ![](https://img.shields.io/badge/mysql-%23323330.svg?&style=for-the-badge&logo=mysql&logoColor=white)

![](https://i.imgur.com/ejWYmTU.png)

# Installation

##### Database

First create a new database in mysql.
Now go to the project and open the file named `config.php` it the backend folder and then change the mysql settings.

| Constants | Change to your MYSQL settings |
| --------- | ----------------------------- |
| HOSTNAME  | "MYSQL HOSTNAME"              |
| DATABASE  | "MYSQL DATABASE"              |
| USERNAME  | "MYSQL USERNAME"              |
| PASSWORD  | "MYSQL PASSWORD"              |

When you are done the project now will be able to connect to
the database and you will be able to start syncing.

##### Syncing Data

To sync the data you just need to execute the file execute.php
in the backend/database folder.

The default start date is 2020-05-01, you can change that in the execute.php
There is data for 191 countries and around 400 records for each day by writing this documentation.
That will take around 3-5 minutes to finish syncing.

![](https://im4.ezgif.com/tmp/ezgif-4-e91c403699d1.gif)
