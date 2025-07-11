# uitm_achievements

Project Name: UiTM Achievements

Purpose:
To showcase achievements by UiTM, either academic, research, student development, recognition, CSR to show the world about UiTM achievements.
This project is intended to help boost Universiti Teknologi MARA (UiTM), international image and branding by providing a mechanism for UiTM to showcase its success stories quickly.
The idea is to have any individual that is connected to UiTM (staff, only people with @uitm.edu.my emails are allowed) to be able to post their success stories and achievements through this website, then the admin verifies the news. If the admin approves, the success story / achievement gets to be shown in the public facing website (need to create this as well) that is dynamically updated as more approved news comes in. To verify the news, admin needs contact information of the person submitting the achievement (email and phone number should suffice). 

There are three main parts:
1. User (Submitter) section. Where he can write a short report, upload pictures and submit YouTube links for video. I also want to collect the contact information of the Person in Charge for the project (this can help interested parties to contact the person directly). The user should also be able to edit his profile, but email change needs to come from 
2. Administrator side interface: for verifying, editing and approving the reports. Need to set system wide settings as well. Also need a chat-like mechanism where the admin and user can talk to each other (two-way feedback mechanism)
3. Public-Facing website: UiTM is a big institution. This system will gather much news and reports from various UiTM people nationwide. The website should be dynamically updating as new entries come in. As there is a lot of information, I need the website to potray it professionally, engaging (use the pictures and videos from the submissions in Step 1) with clean, modern design (Use Bootstrap, Bootstrap.JS, AJAX and Font Awesome Icons to build the interface). The information needs to be organized by these categories Academic, Research, Student development, Industrial Linkages, Internationalization, Recognition & Awards, Corporate Social Responsibility (CSR). Organize the many reports into appealing and engaging content. Public viewers can like the contents posted. Top 5 most liked projects should be automatically featured on the front page of the website. Use Read More to not overwhelm the viewer with too many reports. Create a search and filter feature to help viewers sift through the many reports.

This solves a few problems:
1. Too many congratulations and announcement emails received by UiTM members every day.
2. Achievements in UiTM (with its so many campuses nationwide) can be showcased quickly without relying on layers and layers of bureaucracy. We need to get good achievements quickly to the public.

Design:
There are only two types of users: User and Administrator. Both need to be registered with @uitm.edu.my emails.
User enters their biography, achievement stories, pictures, level (International, National, Institutional), and their contact information and links to their CV, Google Scholar, LinkedIn, Scopus, ISI and ORCID links (let the user fill in as much as possible). 
Administrators edit / gives feedback / approves / rejects the submission by the User. Administrators can add and edit the achievements as well. The reason is we want to ensure that these achievements have quality and deserve to be shown in the public facing website. Admins might need to modify  UiTM achievements as well.
Another component is a forward facing website to showcase UiTM achievements (organize well, i anticipate a lot of entries) and talents (for the users, build automated profiles for them e.g. view_profile?uitm_id=214841). Basically, we want people to know about uitm achievements, see profiles, and contact info for further information.

UI/UX:
Build the UI/UX using Bootstrap, Bootstrap.JS, Font Awesome Icons, AJAX. 

Deployment conditions:
Will be installed on siteground.com
Design the MySQL database schema for me based on these requirements. Save each table in their own MySQL file and store in folder /db.

File and Folder structure (follow closely):
- uitm_achievements
-- index.php
-- assets/
--- uitm_logo.png
-- (other folder, create as you need to help organize)

-- config/
--- config.php

-- includes/
--- navbar.php (shared by Administrators and Users. Limit access based on user role, admins can access both user and admin interfaces as sometimes they may need to submit reports as well. Allow admins full access to user features).
--- user_sidebar.php (User sidebar, Admins should be able to have access to this as well
--- admin_sidebar.php (Administrators only sidebar)
--- header.php (shared by Administrators and Users)
--- footer.php (shared by Administrators and Users)

-- functions/ (group reusable code here, DRY principles)
--- functions.php
--- functions.js
--- style.css
--- ajax.php

-- authentication/
--- login.php
--- forgot_password.php
--- register.php

-- user/
--- index.php
--- dashboard.php
--- update_profile.php
--- (all other user files here, build as you need)

-- admin/
--- index.php
--- dashboard.php
--- (all other admin files here)
--- crud/
---- crud_tablename.php (need list + filter + search + crud access capabilities for all tables created for this project)

-- public/ (this is a forward facing website to showcase UiTM achievements and talents(the users, build automated profiles for them e.g. view_profile?uitm_id=214841)
--- index.php (this is a public facing website where user can see all achievements of UiTM)
--- staff_profile.php?uitm_id=123456
--- view_achievements.php

-- uploads/
--- images/
--- (create necessary folders here to organize)/



