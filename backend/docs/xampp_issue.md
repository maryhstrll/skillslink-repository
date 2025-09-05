1. When MySQL won't start, open
    # C:xampp\mysql\data
- Make a copy file of the `data` file. Name it as (data - Copy)
- Open the `data` file
- Delete all the folders and files EXCEPT `skillslink_db` and `ibdata1`
- Next, open the 
    # C:xampp\mysql\backup
- Copy all except `ibdata`
- Go to the `data` file again and paste what you copied from the `backup` file
## BE CAREFUL IN DOING THESE. ESPECIALLY WHEN DELETING FILES AND FOLDERS


### When Accident Happens at na-delete mo na naman ang `ibdata1` mo lukang programmer, ito na gawin mo since gumawa ka na ng automatic backup for your db, dibaga?
FOLLOW the ff;

2. If MySQL corrupts (ibdata1 lost), 

- reset InnoDB files by deleting them so MySQL can start fresh.

- Then restore your .sql backup using Command Line or phpMyAdmin Import.

# Back-Up Location
### C:backups
- Choose the current backup that was saved

##### Hingi ka na lang ng step-by-step kay AI kapag nalito ka sa PAG-RESTORE NG DB mo.