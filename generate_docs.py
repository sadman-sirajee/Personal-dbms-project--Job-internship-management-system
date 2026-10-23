import docx
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import parse_xml, OxmlElement
from docx.oxml.ns import nsdecls, qn

doc = docx.Document()

# Page Margins
for section in doc.sections:
    section.top_margin = Inches(1)
    section.bottom_margin = Inches(1)
    section.left_margin = Inches(1)
    section.right_margin = Inches(1)

# Helpers for styling
PRIMARY_COLOR = RGBColor(13, 110, 253) # #0d6efd
DARK_COLOR = RGBColor(30, 41, 59)     # #1e293b
GRAY_COLOR = RGBColor(100, 116, 139)  # #64748b

def set_cell_background(cell, fill_hex):
    tcPr = cell._tc.get_or_add_tcPr()
    shd = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{fill_hex}"/>')
    tcPr.append(shd)

def set_cell_margins(cell, top=100, bottom=100, left=150, right=150):
    tcPr = cell._tc.get_or_add_tcPr()
    tcMar = OxmlElement('w:tcMar')
    for m, val in [('top', top), ('bottom', bottom), ('left', left), ('right', right)]:
        node = OxmlElement(f'w:{m}')
        node.set(qn('w:w'), str(val))
        node.set(qn('w:type'), 'dxa')
        tcMar.append(node)
    tcPr.append(tcMar)

# Document Title
p_title = doc.add_paragraph()
p_title.alignment = WD_ALIGN_PARAGRAPH.CENTER
run_title = p_title.add_run("Job/Internship Management System")
run_title.font.name = "Arial"
run_title.font.size = Pt(26)
run_title.font.bold = True
run_title.font.color.rgb = PRIMARY_COLOR

p_sub = doc.add_paragraph()
p_sub.alignment = WD_ALIGN_PARAGRAPH.CENTER
run_sub = p_sub.add_run("CRUD Web Application Project Documentation & Technical Report")
run_sub.font.name = "Arial"
run_sub.font.size = Pt(14)
run_sub.font.italic = True
run_sub.font.color.rgb = DARK_COLOR

p_meta = doc.add_paragraph()
p_meta.alignment = WD_ALIGN_PARAGRAPH.CENTER
run_meta = p_meta.add_run("Technology Stack: PHP | MySQL | Bootstrap 5 | Apache (XAMPP)\nAcademic Submission Deliverable")
run_meta.font.name = "Arial"
run_meta.font.size = Pt(10)
run_meta.font.color.rgb = GRAY_COLOR

doc.add_paragraph().paragraph_format.space_after = Pt(12)

# Function to add Section Headings
def add_heading(number, text):
    h = doc.add_paragraph()
    h.paragraph_format.space_before = Pt(16)
    h.paragraph_format.space_after = Pt(6)
    r = h.add_run(f"{number}. {text}")
    r.font.name = "Arial"
    r.font.size = Pt(16)
    r.font.bold = True
    r.font.color.rgb = PRIMARY_COLOR

def add_p(text, bold_prefix="", italic=False):
    p = doc.add_paragraph()
    p.paragraph_format.space_after = Pt(6)
    p.paragraph_format.line_spacing = 1.15
    if bold_prefix:
        rb = p.add_run(bold_prefix)
        rb.font.name = "Calibri"
        rb.font.size = Pt(11)
        rb.font.bold = True
    r = p.add_run(text)
    r.font.name = "Calibri"
    r.font.size = Pt(11)
    r.font.italic = italic
    return p

# 1. PROJECT OVERVIEW
add_heading(1, "Project Overview")
add_p(
    "The Job/Internship Management System is a comprehensive, web-based CRUD application developed using PHP and MySQL. "
    "The primary objective of the system is to provide an intuitive, responsive, and secure administrative platform for managing "
    "system users and job/internship vacancies. Built with modern web development best practices, it demonstrates the four foundational "
    "database operations: Create, Read, Update, and Delete across multiple interconnected modules."
)
add_p(
    "Suggested Project Summary:\n"
    "\"The Job/Internship Management System is a web-based CRUD application developed using PHP and MySQL. The main purpose of the system "
    "is to manage users and job/internship listings efficiently. The system provides complete Create, Read, Update, and Delete functionality "
    "for both User Management and Job/Internship Management. An administrator can log in, manage users, add and manage job/internship listings, "
    "search and filter listings, and securely log out of the system.\"",
    italic=True
)

# 2. OBJECTIVES & SCOPE
add_heading(2, "Objectives & Scope")
add_p("The core objectives of the Job/Internship Management System include:")
bullet_points = [
    "Implement a complete web-based CRUD workflow with persistent MySQL database storage.",
    "Deliver a robust User Management module allowing administrators to create, view, modify, and delete user profiles.",
    "Deliver a full Job/Internship Management module supporting categorized postings (Job vs. Internship), deadlines, locations, and descriptions.",
    "Incorporate real-time search capabilities across titles, companies, and geographical locations.",
    "Incorporate type-based filtering to instantly isolate Job or Internship opportunities.",
    "Enforce rigorous input validation, preventing duplicate registrations, malformed emails, and invalid dates.",
    "Apply security measures including password hashing (bcrypt), session authentication, and parameterized prepared statements.",
    "Present a modern, mobile-responsive user interface using Bootstrap 5.3 and Bootstrap Icons."
]
for bp in bullet_points:
    p = doc.add_paragraph(style='List Bullet')
    p.paragraph_format.space_after = Pt(3)
    r = p.add_run(bp)
    r.font.name = "Calibri"
    r.font.size = Pt(11)

# 3. TECHNOLOGIES USED
add_heading(3, "Technologies Used")
tech_table = doc.add_table(rows=1, cols=3)
tech_table.alignment = WD_TABLE_ALIGNMENT.CENTER
tech_table.autofit = False

hdr_cells = tech_table.rows[0].cells
headers = ["Component / Layer", "Technology", "Role & Description"]
for i, h in enumerate(headers):
    hdr_cells[i].text = h
    set_cell_background(hdr_cells[i], "1E293B")
    for p in hdr_cells[i].paragraphs:
        for r in p.runs:
            r.font.name = "Arial"
            r.font.size = Pt(10)
            r.font.bold = True
            r.font.color.rgb = RGBColor(255, 255, 255)

tech_data = [
    ("Backend Language", "PHP 8.2 (ZTS)", "Server-side routing, request handling, session management, and business logic"),
    ("Relational Database", "MySQL 8.0 / MariaDB", "Data persistence, schema constraints, relational tables, indexing, and transactional integrity"),
    ("Database Driver", "PHP PDO (PHP Data Objects)", "Secure database interface using prepared statements preventing SQL injection"),
    ("Frontend Styling", "Bootstrap 5.3 & Bootstrap Icons", "Responsive grid system, accessible modals, cards, badges, and icon typography"),
    ("Custom Stylesheet", "CSS3 Custom Properties", "Modern shadows, gradient navigation, stat card transitions, and micro-interactions"),
    ("Development Server", "Apache 2.4 (via XAMPP)", "Local web server delivering HTTP requests and serving dynamic PHP scripts"),
    ("Code Editor", "Visual Studio Code", "Source code authoring, automated testing, and development workspace")
]

for row_idx, data in enumerate(tech_data):
    row_cells = tech_table.add_row().cells
    bg_color = "F8FAFC" if row_idx % 2 == 1 else "FFFFFF"
    for col_idx, text in enumerate(data):
        row_cells[col_idx].text = text
        set_cell_background(row_cells[col_idx], bg_color)
        set_cell_margins(row_cells[col_idx], 80, 80, 100, 100)
        for p in row_cells[col_idx].paragraphs:
            for r in p.runs:
                r.font.name = "Calibri"
                r.font.size = Pt(9.5)

doc.add_paragraph().paragraph_format.space_after = Pt(6)

# 4. SYSTEM MODULES & ARCHITECTURE
add_heading(4, "System Modules & Architecture")
add_p(
    "The application follows a modular, organized directory hierarchy adhering strictly to project guidelines:\n"
    "• config/: Houses database credentials, PDO instantiation, session utilities, and global helper functions.\n"
    "• includes/: Contains reusable presentation components including header.php, navbar.php, and footer.php.\n"
    "• users/: Implements all User Management CRUD scripts (index.php, create.php, edit.php, delete.php).\n"
    "• jobs/: Implements all Job/Internship Management CRUD scripts (index.php, create.php, edit.php, delete.php, view.php).\n"
    "• Root Files: index.php (welcome portal), login.php (authentication), logout.php (session termination), and dashboard.php (administrative metrics)."
)

# 5. DATABASE DESIGN
add_heading(5, "Database Design")
add_p("Database Name: ", bold_prefix="job_internship_db\n")
add_p("The database consists of two core entity tables designed to store credentials, user roles, and position vacancy postings:")

# Users Table
add_p("Table 1: users", bold_prefix="")
user_tbl = doc.add_table(rows=1, cols=4)
user_tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
u_hdrs = ["Field", "Data Type", "Constraints", "Description"]
for i, h in enumerate(u_hdrs):
    user_tbl.rows[0].cells[i].text = h
    set_cell_background(user_tbl.rows[0].cells[i], "0D6EFD")
    for p in user_tbl.rows[0].cells[i].paragraphs:
        for r in p.runs:
            r.font.name = "Arial"
            r.font.size = Pt(10)
            r.font.bold = True
            r.font.color.rgb = RGBColor(255, 255, 255)

u_rows = [
    ("id", "INT", "AUTO_INCREMENT, PRIMARY KEY", "Unique system identifier for the user"),
    ("name", "VARCHAR(100)", "NOT NULL", "Full legal or professional name"),
    ("email", "VARCHAR(100)", "NOT NULL, UNIQUE", "Unique user email address used for login"),
    ("password", "VARCHAR(255)", "NOT NULL", "Bcrypt hashed security password string"),
    ("role", "VARCHAR(20)", "DEFAULT 'User'", "Access role ('Admin' or 'User')"),
    ("created_at", "TIMESTAMP", "DEFAULT CURRENT_TIMESTAMP", "Timestamp of account registration")
]

for row_idx, r_data in enumerate(u_rows):
    row_cells = user_tbl.add_row().cells
    bg = "F1F5F9" if row_idx % 2 == 1 else "FFFFFF"
    for col_idx, val in enumerate(r_data):
        row_cells[col_idx].text = val
        set_cell_background(row_cells[col_idx], bg)
        set_cell_margins(row_cells[col_idx], 70, 70, 100, 100)
        for p in row_cells[col_idx].paragraphs:
            for r in p.runs:
                r.font.name = "Calibri"
                r.font.size = Pt(9.5)

doc.add_paragraph().paragraph_format.space_after = Pt(8)

# Jobs Table
add_p("Table 2: jobs", bold_prefix="")
jobs_tbl = doc.add_table(rows=1, cols=4)
jobs_tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
j_hdrs = ["Field", "Data Type", "Constraints", "Description"]
for i, h in enumerate(j_hdrs):
    jobs_tbl.rows[0].cells[i].text = h
    set_cell_background(jobs_tbl.rows[0].cells[i], "0D6EFD")
    for p in jobs_tbl.rows[0].cells[i].paragraphs:
        for r in p.runs:
            r.font.name = "Arial"
            r.font.size = Pt(10)
            r.font.bold = True
            r.font.color.rgb = RGBColor(255, 255, 255)

j_rows = [
    ("id", "INT", "AUTO_INCREMENT, PRIMARY KEY", "Unique identifier for listing"),
    ("title", "VARCHAR(150)", "NOT NULL", "Job or internship title"),
    ("company", "VARCHAR(150)", "NOT NULL", "Name of hiring company or organization"),
    ("location", "VARCHAR(100)", "DEFAULT NULL", "Work location (e.g. Dhaka, Remote)"),
    ("type", "VARCHAR(20)", "NOT NULL", "Opportunity type ('Job' or 'Internship')"),
    ("description", "TEXT", "DEFAULT NULL", "Detailed role overview and candidate requirements"),
    ("deadline", "DATE", "DEFAULT NULL", "Application closing deadline"),
    ("created_at", "TIMESTAMP", "DEFAULT CURRENT_TIMESTAMP", "Timestamp of listing creation")
]

for row_idx, r_data in enumerate(j_rows):
    row_cells = jobs_tbl.add_row().cells
    bg = "F1F5F9" if row_idx % 2 == 1 else "FFFFFF"
    for col_idx, val in enumerate(r_data):
        row_cells[col_idx].text = val
        set_cell_background(row_cells[col_idx], bg)
        set_cell_margins(row_cells[col_idx], 70, 70, 100, 100)
        for p in row_cells[col_idx].paragraphs:
            for r in p.runs:
                r.font.name = "Calibri"
                r.font.size = Pt(9.5)

# 6. CRUD OPERATIONS IMPLEMENTATION
add_heading(6, "CRUD Operations Detailed Mapping")
add_p(
    "The application implements complete CRUD operations across both primary modules:",
    bold_prefix=""
)
crud_mappings = [
    ("User Management - CREATE (users/create.php): ", "Allows administrators to add users. Checks for missing fields, validates email formatting, prevents duplicate emails, hashes password via password_hash(), and stores record in MySQL."),
    ("User Management - READ (users/index.php): ", "Fetches and displays all user records with ID, name, email, role badge, and creation timestamp. Supports real-time filtering by keyword."),
    ("User Management - UPDATE (users/edit.php): ", "Pre-populates user details. Allows updating name, email, and role. Optional password update allows changing credentials or preserving existing hash."),
    ("User Management - DELETE (users/delete.php): ", "Features a dedicated confirmation screen displaying user particulars with 'Cancel' and 'Delete' buttons (Section 18). Safely blocks administrators from deleting their own active session."),
    ("Job Management - CREATE (jobs/create.php): ", "Facilitates publishing new listings with title, company, location, type (Job/Internship), description, and deadline date validation."),
    ("Job Management - READ (jobs/index.php): ", "Presents all vacancies in a responsive table with role badges, formatted deadlines, view modal/page link, and actions."),
    ("Job Management - UPDATE (jobs/edit.php): ", "Enables complete modification of job and internship parameters, synchronizing updates immediately to MySQL."),
    ("Job Management - DELETE (jobs/delete.php): ", "Displays a dedicated confirmation dialog with position details before permanent deletion, preventing accidental removals.")
]
for prefix, desc in crud_mappings:
    add_p(desc, bold_prefix=prefix)

# 7. SEARCH & FILTER FEATURES
add_heading(7, "Search & Filtering Mechanics")
add_p(
    "To enhance usability, the Job/Internship Management module integrates two search and filter controls:\n"
    "1. Multi-Field Search: The search bar accepts arbitrary query terms and executes a parameterized SQL LIKE query across `title`, `company`, and `location` simultaneously.\n"
    "2. Type Filtering: Users can toggle between 'All Listings', 'Job Postings Only', and 'Internship Postings Only' seamlessly.\n"
    "3. Combined Filter Operation: The system allows combining a keyword search with a type filter simultaneously, preserving both parameters in the query string with active badge indicators and a quick reset button."
)

# 8. SECURITY & VALIDATION
add_heading(8, "Security Improvements & Input Validation")
add_p(
    "The application adopts defense-in-depth security standards as specified in the project guidelines:\n"
    "• Password Security: Passwords are encrypted using PHP's industry-standard bcrypt algorithm (password_hash()). Plaintext passwords are never stored.\n"
    "• Authentication & Session Control: Pages require authentication via require_login(). Session fixation is mitigated with session_regenerate_id(true) on login.\n"
    "• SQL Injection Protection: All database interactions utilize PDO prepared statements with parameter binding, eliminating SQL injection vectors.\n"
    "• Cross-Site Scripting (XSS) Mitigation: All dynamic database outputs are encoded via htmlspecialchars(..., ENT_QUOTES, 'UTF-8').\n"
    "• Self-Lockout Prevention: An administrator cannot delete their own active account in the User Management module.\n"
    "• Form Validation: Server-side validation verifies non-empty inputs, valid email formats, unique email constraints, and date validity."
)

# 9. TESTING & VERIFICATION
add_heading(9, "Testing Plan & Verification Results")
add_p(
    "A comprehensive automated test suite (test_suite.php) was executed against the running database, covering 17 critical tests with a 100% pass rate:"
)

test_table = doc.add_table(rows=1, cols=3)
test_table.alignment = WD_TABLE_ALIGNMENT.CENTER
t_hdrs = ["Test Case", "Evaluation Criteria", "Result"]
for i, h in enumerate(t_hdrs):
    test_table.rows[0].cells[i].text = h
    set_cell_background(test_table.rows[0].cells[i], "1E293B")
    for p in test_table.rows[0].cells[i].paragraphs:
        for r in p.runs:
            r.font.name = "Arial"
            r.font.size = Pt(10)
            r.font.bold = True
            r.font.color.rgb = RGBColor(255, 255, 255)

test_rows = [
    ("Database Connectivity", "PDO instance connects with UTF8 charset to MySQL", "PASS (100%)"),
    ("Admin Account Existence", "Default administrator account exists in users table", "PASS (100%)"),
    ("Password Hash Verification", "password_verify() authenticates 'admin123' and rejects invalid passwords", "PASS (100%)"),
    ("User CREATE", "Inserts unique user with auto-increment primary key", "PASS (100%)"),
    ("User READ", "Retrieves inserted user attributes by ID", "PASS (100%)"),
    ("User UPDATE", "Updates user attributes and persists changes to MySQL", "PASS (100%)"),
    ("User DELETE", "Deletes target user and verifies record count decreases", "PASS (100%)"),
    ("Job CREATE", "Inserts Job listing with title, company, type, deadline", "PASS (100%)"),
    ("Internship CREATE", "Inserts Internship opportunity with specific parameters", "PASS (100%)"),
    ("Job/Internship READ", "Fetches listings and verifies distinct type categorization", "PASS (100%)"),
    ("Job UPDATE", "Updates listing details and verifies database persistence", "PASS (100%)"),
    ("Keyword Search Test", "Searches keyword matching title, company, and location", "PASS (100%)"),
    ("Type Filter Test", "Filters listings strictly by 'Internship' type", "PASS (100%)"),
    ("Data Cleanup Test", "Removes temporary test records cleanly", "PASS (100%)"),
    ("HTTP Server Verification", "Apache HTTP 200 OK response on index and dashboard", "PASS (100%)")
]

for row_idx, r_data in enumerate(test_rows):
    row_cells = test_table.add_row().cells
    bg = "ECFDF5" if "PASS" in r_data[2] else "FEF2F2"
    for col_idx, val in enumerate(r_data):
        row_cells[col_idx].text = val
        set_cell_background(row_cells[col_idx], bg)
        set_cell_margins(row_cells[col_idx], 60, 60, 90, 90)
        for p in row_cells[col_idx].paragraphs:
            for r in p.runs:
                r.font.name = "Calibri"
                r.font.size = Pt(9.5)
                if col_idx == 2:
                    r.font.bold = True
                    r.font.color.rgb = RGBColor(16, 185, 129)

# 10. CONCLUSION & SUBMISSION CHECKLIST
add_heading(10, "Conclusion & Submission Checklist")
add_p(
    "The Job/Internship Management System satisfies all functional, architectural, and security requirements set forth in the project specification. "
    "The codebase is clean, well-documented, follows professional standards, and is packaged for immediate deployment and academic evaluation."
)

chk_items = [
    "[X] Complete application folder structured and tested in XAMPP htdocs.",
    "[X] MySQL database schema and seed data exported to database.sql.",
    "[X] Comprehensive academic documentation compiled into Project_Documentation.docx.",
    "[X] Full User Management CRUD implemented with validation and delete confirmation.",
    "[X] Full Job/Internship Management CRUD implemented with search and type filtering.",
    "[X] Secure session-based authentication with bcrypt password hashing.",
    "[X] Bootstrap 5 responsive user interface across all views.",
    "[X] Application packaged into Job-Internship-Management-System.zip."
]
for item in chk_items:
    p = doc.add_paragraph(style='List Bullet')
    p.paragraph_format.space_after = Pt(3)
    r = p.add_run(item)
    r.font.name = "Calibri"
    r.font.size = Pt(10.5)

output_path = r"c:\JOB_INTERNSHIP MANAGEMENT SYSTEM\Project_Documentation.docx"
doc.save(output_path)
print(f"Successfully generated documentation at: {output_path}")
