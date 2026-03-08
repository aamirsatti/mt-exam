# mt-exam
A WordPress plugin for managing exams, students, and results
# Exam Management Plugin  


---
## Installation

. Plugin Features:<br>
   Students, Subjects, Exams, Results<br>
   Results → Bulk Import<br>
   Students → Statistics Report<br>
Included Files<br>
exam.php — Main plugin file<br>
classes/ — classes (CPTs, meta boxes, AJAX, shortcodes, import, report)<br>
sample-CSV.csv — Example CSV format for bulk import<br>

Testing Instructions<br>
Create a Term: T1 2026-26, T2 2026-15<br>
Create Subjects: Business Management, Cloud Computing<br>
Create Students: Ali, Mohamed,  For Students I have created Class taxonomy<br>
Create Exam: Midterm (2025-10-15), subjects: Business Management + Cloud Computing, term: T1 2026-26<br>
Create Results:<br>
> Select Exam ,   Select Class (Optional)<br>
Add marks of Students <br/>
Verify:
Shortcode [em_top_students] shows Top 3 Students of each Term<br>
Statistics Report shows full breakdown<br>
Bulk Import accepts sample-CSV.csv<br>

