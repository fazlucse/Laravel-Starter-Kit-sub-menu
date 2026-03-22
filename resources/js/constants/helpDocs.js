export const HELP_DOCS = {
    users: {
        title: "User Management System: Operations Manual",
        'description': '',
        // Combine them into a "Groups" array
        full_docs: {
            title: "Complete User Guide",
            groups: [
                {
                    group_title: "List & Table Guidelines",
                    sections: [
                        {
                            label: "Add User",
                            content: "Click the blue button to create a new system identity and set initial credentials.",
                            icon: "UserPlus"
                        },
                        {
                            label: "Edit Action",
                            content: "Located in the Actions column; use the Pencil icon to modify permissions or the Trash icon to delete.",
                            icon: "Settings"
                        },
                        {
                            label: "Mobile Access",
                            content: "Green indicates mobile login is enabled. Red restricts access to Office Desktops only.",
                            icon: "Smartphone"
                        },
                        {
                            label: "Status",
                            content: "Active (Green) users can log in, while Inactive (Red) accounts are currently locked.",
                            icon: "UserCheck"
                        },
                        {
                            label: "Access Days",
                            content: "Displays blue badges for authorized login days (e.g., Mon, Tue).",
                            icon: "CalendarDays"
                        }
                    ]
                },
                {
                    group_title: "Form & Entry Guidelines",
                    sections: [
                        {
                            label: "Employee",
                            content: "Select an employee from the dropdown to automatically pull their Full Name and Official Email from the HR database for new system user creation.",
                            icon: "Zap"
                        },
                        {
                            label: "User Roles",
                            content: "Assign Admin, Manager, or User permissions to define which system menus are visible.",
                            icon: "ShieldCheck"
                        },
                        {
                            label: "Password Management",
                            content: "Use the Eye Icon to toggle visibility. Enter a password for system login and re-type to confirm; when editing, leave both blank to keep the existing password.",
                            icon: "Lock"
                        },
                        {
                            label: "Access Controls",
                            content: "Toggle specific work days or restrict mobile login to enforce office-only security.",
                            icon: "CheckSquare"
                        }
                    ]
                }
            ]
        }
    },
    people: {
        title: "People Management",
        description: "The primary architectural foundation for all personnel data. Every individual must be registered in this database as a mandatory prerequisite before they can be officially onboarded as an employee or granted system access.",

        tips: [
            "Always register a person **before assigning them as an employee**.",
            "Ensure the phone number and full name are correct for payroll and system login.",
            "Use consistent country/city data for reporting purposes.",
            "Upload a clear profile photo for identity verification; initials are used if missing.",
            "Double-check gender selection to correctly enable maternity/paternity leave rules.",
            "Regularly update records to maintain system integrity."
        ], full_docs: {
            title: "People Module: Complete Manual",
            groups: [
                {
                    group_title: "1. Navigation & List Guidelines",
                    sections: [
                        {
                            label: "Person Directory (List View)",
                            content: "The list displays core staff details: **ID, Full Name, Designation, Phone, and Email** for quick contact and identification.",
                            icon: "LayoutList",
                            image: "/docs/person-list.png" // Screenshot of your table
                        },
                        {
                            label: "Register New Person",
                            content: "Register New Person, click the **'+ Add'** button. This opens the registration form to create a new identity in the system.",
                            icon: "UserPlus",
                            image: "/docs/person-form.png"
                        },
                        {
                            label: "Search Popup",
                            content: "Find staff instantly by Name or ID. Use the 'S.L' column for serial reference.",
                            icon: "Zap"
                        },
                        {
                            label: "Action Actions",
                            content: "Blue Eye: View Profile | Yellow Pencil: Update Info | Red Trash: Delete.",
                            icon: "MousePointer2"
                        },
                        {
                            label: "Photo & Initials",
                            content: "Photos appear in circles. If missing, a colored avatar with initials is automatically shown.",
                            icon: "UserCheck"
                        }
                    ]
                },
                {
                    group_title: "2. Profile Entry Rules",
                    sections: [
                        {
                            label: "Mandatory Data",
                            content: "Full Name and Phone are required for database integrity and payroll generation.",
                            icon: "Lock"
                        },
                        {
                            label: "Dynamic Logic",
                            content: "Gender must be accurate (Male/Female) to enable specific leave types like Maternity later.",
                            icon: "ShieldCheck"
                        },
                        {
                            label: "Location Mapping",
                            content: "Selecting a Country and City is required for transport and regional reporting.",
                            icon: "Smartphone"
                        },
                        {
                            label: "Photo Policy",
                            content: "Supported: JPG/PNG. Ensure the face is clear for system identity verification.",
                            icon: "Smartphone"
                        }
                    ]
                }
            ]
        }
    },
    employees: {
        title: "Employee Management",
        description: "The operational engine for workforce administration. This module transforms registered individuals into official staff members by defining their professional hierarchy, compensation structures, and employment terms.",

        tips: [
            "A record must exist in the **People Module** before an Employee can be registered.",
            "Accurate **Joining Dates** are critical for calculating service length and leave accruals.",
            "Use the **Gross Salary** field to auto-calculate basic and allowance breakdowns.",
            "Define **Reporting Managers** correctly to ensure automated approval workflows function.",
            "Ensure **Office In/Out times** are set to monitor attendance and late-entry deductions.",
            "Check **Bank Details** twice to prevent salary disbursement failures."
        ],

        full_docs: {
            title: "Employee Module: Operational Manual",
            groups: [
                {
                    group_title: "1. Workforce Inventory (List View)",
                    sections: [
                        {
                            label: "Employee ID",
                            content: "The unique corporate identifier (e.g., EMP-001). This is separate from the Person ID.",
                            icon: "ShieldCheck",
                            image: "/docs/employee-list.png"
                        },
                        {
                            label: "Status Tracking",
                            content: "Monitors the lifecycle: Active, Probation, Resigned, or Terminated.",
                            icon: "UserCheck"
                        },
                        {
                            label: "Quick Actions",
                            content: "Blue Eye: Professional File | Yellow Pencil: Contract Update | Red Trash: Termination.",
                            icon: "MousePointer2"
                        },
                        {
                            label: "Global Search",
                            content: "Filter the entire workforce by Department, Company, or Designation instantly.",
                            icon: "Zap"
                        }
                    ]
                },
                {
                    group_title: "2. Registration Steps(Form)",
                    sections: [

                        {
                            label: "Personal Info",
                            content: "Enter the employee's basic details including Gender, Marital Status, Blood Group, and specific Skills. Ensure the 'Person' search matches their basic profile.",
                            icon: "UserCheck",
                            image: "/docs/employee-form.png" // The image you uploaded
                        },
                        {
                            label: "Company & Employment",
                            content: "Define the Official ID, Designation, Department, and Reporting Manager. Set the Joining Date to trigger attendance tracking.",
                            icon: "LayoutList",
                        },
                        {
                            label: " Salary & Banking",
                            content: "Configure the Gross Salary breakdown and provide Bank Account or Mobile Banking (e.g., bKash/Nagad) details for automated disbursements.",
                            icon: "ClipboardPen",
                        },

                        {
                            label: "Salary Logic",
                            content: "Entering a Gross Salary automatically distributes 50% to Basic and 30% to House Rent.",
                            icon: "DollarSign"
                        },
                        {
                            label: "Attendance Rules",
                            content: "Define Shift times and Late-time thresholds (minutes) for automated payroll deductions.",
                            icon: "CalendarDays"
                        },
                        {
                            label: "Banking & Tax",
                            content: "Store Bank Account, PAN/TIN, and Tax status for legal financial compliance.",
                            icon: "CreditCard"
                        }
                    ]
                }
            ]
        }
    },
    attendance: {
        title: "Attendance Management",
        description: "The primary time-tracking engine for the organization. This module monitors daily shift compliance, calculates late-in/early-out deviations, and provides the raw data required for automated monthly payroll deductions.",

        tips: [
            "Attendance can be entered **Individually** for corrections or uploaded in **Bulk via Excel**.",
            "The system automatically compares 'Employee Time' against the 'Office Time' defined in their contract.",
            "Ensure the Date format in Excel matches the system requirement (YYYY-MM-DD) to avoid upload errors.",
            "Late In and Early Out times are calculated in minutes and displayed in a human-readable H/M format.",
            "Deleting an attendance record is permanent and will directly impact the employee's monthly 'Days Worked' count.",
            "Use the 'Searchable Select' in manual entry to quickly find employees by their Name or Person ID."
        ],
        excel_format: {
            title: "Required Excel Structure",
            headers: ["Employee Name", "Employee ID", "Date", "In Time", "Out Time"],
            rows: [
                ["Test User", "EMP-101", "2026-03-22", "09:00 AM", "05:00 PM"],
                ["Test User", "EMP-102", "2026-03-22", "09:15 AM", "06:00 PM"]
            ],
            note: "Files must be in .xlsx or .csv format. Ensure 'Date' is YYYY-MM-DD."
        },
        full_docs: {
            title: "Attendance Module: Operational Manual",
            groups: [
                {
                    group_title: "1. Attendance Monitoring (List View)",
                    sections: [
                        {
                            label: "Shift Comparison",
                            content: "Displays both 'Office In/Out' (the requirement) and 'Emp In/Out' (the reality) side-by-side.",
                            icon: "CalendarDays",
                            image: "/docs/attendance-list.png"
                        },
                        {
                            label: "Deviation Logic",
                            content: "Late In: Employee arrived after Office In time. Early Out: Employee left before Office Out time.",
                            icon: "Zap"
                        },
                        {
                            label: "Time Formatting",
                            content: "All times are converted to 12-hour format (AM/PM) for easier readability in reports.",
                            icon: "InfoIcon"
                        },
                        {
                            label: "Record Status",
                            content: "Indicates the nature of the entry (e.g., Present, Late, or Manual Adjustment).",
                            icon: "UserCheck"
                        }
                    ]
                },
                {
                    group_title: "2. Data Ingestion & Entry (Form)",
                    sections: [
                        {
                            label: "Individual Attendance Entry",
                            content: "Select an employee, choose the date, and set punch times manually to fix biometric errors or log outdoor duties.",
                            icon: "MousePointer2",
                            image: "/docs/attendance-form.png"
                        },
                        {
                            label: "Bulk Excel Upload",
                            content: "The most efficient way to sync biometric machine data. Drag and drop the .xlsx file to process all staff at once.",
                            icon: "ClipboardPen"
                        },
                        {
                            label: "Real-time Validation",
                            content: "The system checks for duplicate entries on the same date for the same employee during upload.",
                            icon: "ShieldCheck"
                        },
                        {
                            label: "Mobile Compatibility",
                            content: "The upload interface supports touch-and-select for mobile browsers if files are stored on the device.",
                            icon: "Smartphone"
                        }
                    ]
                }
            ]
        }
    },
    leave: {
        title: "Leave Management",
        description: "The central gateway for managing employee time-off. This module automates leave eligibility, enforces gender-specific policies, and tracks cumulative balances (AL, CL, SL) to ensure accurate payroll integration.",

        tips: [
            "You can apply for yourself (**Self**) or on behalf of others (**Other**) if you have permissions.",
            "The **Leave Reason** list is dynamic; it automatically filters options like 'Maternity' or 'Paternity' based on the employee's gender.",
            "Use the **Add to List** button to bundle multiple dates or different leave types into a single submission.",
            "For **Half Day** requests, the 'From Date' and 'To Date' must be the same.",
            "A **Reliever** must be assigned for every request to ensure operational continuity during the absence.",
            "Once submitted, requests remain in **Pending** status until approved by the designated manager."
        ],

        full_docs: {
            title: "Leave Module: Operational Manual",
            groups: [
                {
                    group_title: "1. Leave Inventory (List View)",
                    sections: [
                        {
                            label: "Balance Overview",
                            content: "Displays real-time usage of Annual (AL), Casual (CL), Sick (SL), and Parental (MAT/PAT) leaves.",
                            icon: "LayoutList"
                        },
                        {
                            label: "Status Indicators",
                            content: "Yellow: Pending review | Green: Approved/Balance Deducted | Red: Rejected.",
                            icon: "UserCheck"
                        },
                        {
                            label: "Action Permissions",
                            content: "You can only Edit or Delete a request while it is in 'Pending' status.",
                            icon: "Lock"
                        },
                        {
                            label: "Verification",
                            content: "Use the Blue Eye icon to view the full audit trail, including remarks and reliever details.",
                            icon: "MousePointer2"
                        }
                    ]
                },
                {
                    group_title: "2. Application Protocol (Form)",
                    sections: [
                        {
                            label: "Gender-Aware Logic",
                            content: "The system validates the 'Person ID' to ensure the requested leave type matches the employee's profile.",
                            icon: "ShieldCheck"
                        },
                        {
                            label: "Multi-Entry System",
                            content: "The 'Add to List' feature allows you to build a complex leave schedule before final submission.",
                            icon: "Zap"
                        },
                        {
                            label: "Duration Control",
                            content: "Select 'First Half' or 'Second Half' for 0.5-day increments, or 'Full Day' for standard 1.0-day units.",
                            icon: "CalendarDays"
                        },
                        {
                            label: "Reliever Assignment",
                            content: "Mandatory selection of a staff member who will cover duties during the leave period.",
                            icon: "ClipboardPen"
                        },
                        {
                            label: "Emergency Protocol",
                            content: "Requires an 'Emergency Contact Name' to ensure the employee is reachable if urgent issues arise.",
                            icon: "Smartphone"
                        }
                    ]
                }
            ]
        }
    },
    leave_allotment: {
        title: "Leave Allotment",
        description: "The strategic policy layer for time-off management. This module defines the annual leave quotas for employees, establishing the maximum entitlement for each leave category (Annual, Sick, Casual, etc.) per fiscal year.",

        tips: [
            "Allotments are usually set at the start of the **Fiscal Year** to reset employee balances.",
            "Use the **'All Employees'** scope to quickly apply a standard leave policy across the entire organization.",
            "Use **'Individual'** scope for exceptions, such as granting extra leave to a specific senior manager or long-term employee.",
            "The list view shows **Utilized/Allotment** (e.g., 5/20), allowing you to see exactly how much balance remains for every staff member.",
            "Maternal and Paternal allotments are managed here but will only be visible to the employee if their gender matches the policy.",
            "Earned Leave (EL) can be manually adjusted here based on the previous year's carry-forward rules."
        ],

        full_docs: {
            title: "Leave Allotment: Policy Manual",
            groups: [
                {
                    group_title: "1. Balance Tracking (List View)",
                    sections: [
                        {
                            label: "Utilization Ratio",
                            content: "Displays 'Days Used / Total Days'. Example: 2/14 means the employee used 2 days out of 14 allowed.",
                            icon: "LayoutList"
                        },
                        {
                            label: "Leave Categories",
                            content: "Separate tracking for Annual (AL), Casual (CL), Sick (SL), and Earned (EL) leave types.",
                            icon: "CalendarDays"
                        },
                        {
                            label: "LWP Monitoring",
                            content: "Tracks Leave Without Pay (LWP) instances which directly impact payroll deductions.",
                            icon: "DollarSign"
                        },
                        {
                            label: "Photo Verification",
                            content: "Avatars ensure you are viewing the correct employee's balance at a glance.",
                            icon: "UserCheck"
                        }
                    ]
                },
                {
                    group_title: "2. Allotment Configuration (Form)",
                    sections: [
                        {
                            label: "Fiscal Year Mapping",
                            content: "Essential for historical reporting. Ensures leave balances reset correctly every year.",
                            icon: "CalendarDays"
                        },
                        {
                            label: "Bulk Allocation",
                            content: "Selecting 'All Employees' automates the assignment process, saving time during yearly setups.",
                            icon: "Zap"
                        },
                        {
                            label: "Individual Overrides",
                            content: "Allows custom quotas for specific employees who may have unique contract terms.",
                            icon: "MousePointer2"
                        },
                        {
                            label: "Audit Remarks",
                            content: "A mandatory space to document why a specific allotment was granted or modified.",
                            icon: "ClipboardPen"
                        },
                        {
                            label: "Security & Lock",
                            content: "Only users with 'leave-allotment.create' permissions can modify these sensitive quotas.",
                            icon: "ShieldCheck"
                        }
                    ]
                }
            ]
        }
    },
    holidays: {
        title: "Holiday Management",
        description: "The organizational calendar engine. This module defines official non-working days, public holidays, and special observances, ensuring that attendance records and payroll calculations correctly account for authorized office closures.",

        tips: [
            "Holidays prevent the system from marking employees as 'Absent' on those specific dates.",
            "Use **'Person-wise'** mode for restricted holidays that apply only to specific individuals (e.g., religious observances).",
            "Target specific **Departments** or **Divisions** if a holiday only applies to a certain branch or unit.",
            "Setting a **Date Range** (Date From to Date To) allows you to create multi-day holidays like Eid, Diwali, or Christmas breaks in one entry.",
            "Remarks are useful for documenting the government circular or official reason behind the holiday.",
            "Ensure you select the correct **Holiday Type** (Public, Optional, or Company-specific) for accurate leave reporting."
        ],

        full_docs: {
            title: "Holiday Module: Configuration Manual",
            groups: [
                {
                    group_title: "1. Calendar Overview (List View)",
                    sections: [
                        {
                            label: "Holiday Schedule",
                            content: "Displays the designated dates and types of holidays currently active in the system.",
                            icon: "CalendarDays"
                        },
                        {
                            label: "Scope Verification",
                            content: "Shows whether the holiday is Global (All), Department-specific, or restricted to specific Individuals.",
                            icon: "LayoutList"
                        },
                        {
                            label: "Record Management",
                            content: "Use the Red Trash icon to remove a holiday. This will immediately revert the day to a standard working day.",
                            icon: "MousePointer2"
                        },
                        {
                            label: "Remark Tracking",
                            content: "Provides context for the holiday, ensuring transparency for HR audits and employee queries.",
                            icon: "InfoIcon"
                        }
                    ]
                },
                {
                    group_title: "2. Holiday Configuration (Form)",
                    sections: [
                        {
                            label: "Date Range Control",
                            content: "Input a start and end date to define the duration. The system automatically calculates the total non-working days.",
                            icon: "CalendarDays"
                        },
                        {
                            label: "Person-wise Mode",
                            content: "A powerful toggle that allows you to input a comma-separated list of Employee IDs (e.g., 101, 202) for restricted holidays.",
                            icon: "UserCheck"
                        },
                        {
                            label: "Operational Scope",
                            content: "Target specific Offices, Divisions, or Departments. Ideal for regional holidays that don't apply to the head office.",
                            icon: "Building2"
                        },
                        {
                            label: "Category Classification",
                            content: "Assigning a 'Holiday Type' helps the system categorize the day for annual time-off reports.",
                            icon: "ShieldCheck"
                        },
                        {
                            label: "Validation Engine",
                            content: "The system prevents overlapping holiday entries to ensure calendar integrity.",
                            icon: "Zap"
                        }
                    ]
                }
            ]
        }
    },
    movement: {
        title: "Movement Register",
        description: "A real-time logistics and conveyance tracking system. This module records official employee transitions between locations, monitors 'In-Progress' trips, and automates the approval workflow for travel-related expense reimbursements.",

        tips: [
            "A movement is a two-step process: **Start Movement** when leaving and **Finish & Save** upon arrival.",
            "Trips that haven't been 'Finished' will show as **'In Progress'** and cannot be approved or printed.",
            "You can select multiple **Purposes** or **Means of Transport** if your trip involves several stages.",
            "For **Customer Visits**, selecting the specific customer name is mandatory for sales-tracking integrity.",
            "Managers can **Bulk Approve** multiple movements at once from the list view to speed up reimbursement.",
            "If a movement is **Rejected**, the manager must provide a reason which will be visible to the employee."
        ],

        full_docs: {
            title: "Movement Module: Logistics Manual",
            groups: [
                {
                    group_title: "1. Movement Tracking (List View)",
                    sections: [
                        {
                            label: "Live Status",
                            content: "Pending (Yellow), Approved (Green), Rejected (Red). 'In Progress' indicates a trip that hasn't ended yet.",
                            icon: "Zap"
                        },
                        {
                            label: "Bulk Operations",
                            content: "Select multiple checkboxes to Approve or Reject pending items simultaneously.",
                            icon: "CheckSquare"
                        },
                        {
                            label: "Print Reports",
                            content: "Only 'Approved' movements can be printed. Use the Emerald button to generate a reimbursement slip.",
                            icon: "Printer"
                        },
                        {
                            label: "Location Color Coding",
                            content: "F: (Blue) indicates the Origin/From location. T: (Emerald) indicates the Destination/To location.",
                            icon: "MousePointer2"
                        }
                    ]
                },
                {
                    group_title: "2. Trip Documentation (Form)",
                    sections: [
                        {
                            label: "Location & Purpose",
                            content: "Select your 'From' and 'To' locations (e.g., Office to Factory) and choose the purpose of your visit.",
                            icon: "MapPin",
                            image: "/docs/movement-form.png" // The image you uploaded
                        },
                        {
                            label: "Start & End Tracking",
                            content: "Click 'Start Movement' to begin tracking. Once your duty is finished, return to this module to 'End' the session. Requires Visit Category (Customer/Dev), Transport Mode, and Final Bill.",
                            icon: "Zap",
                        },

                        {
                            label: "Conveyance Billing",
                            content: "Input the 'Actual Conveyance Bill'. This amount is what the manager will review for approval.",
                            icon: "DollarSign"
                        },
                        {
                            label: "Rejection Protocol",
                            content: "If a manager rejects a bill, the status will show the specific 'Rejection Reason' provided.",
                            icon: "XCircle"
                        }
                    ]
                }
            ]
        }
    },
    payroll: {
        title: "Payroll & Compensation",
        description: "The financial heart of the HRIS. This module orchestrates the transition from attendance and contract data into accurate financial disbursements, managing salary snapshots, bonus allocations, and statutory deductions.",

        tips: [
            "Always ensure **Attendance** and **Leave Requests** for the month are finalized before running payroll.",
            "Use **'Individual Mode'** for mid-month settlements or specific salary corrections.",
            "The **'Save as Draft'** option allows you to review the calculation in the Preview Modal without locking the records.",
            "Bonus generation can be run independently or alongside salary using the **Bonus Only** toggle.",
            "Once a batch is **Approved**, it is locked for audit integrity and cannot be deleted or modified.",
            "Check the **'Net Payable'** column in the preview to verify that all deductions (Late-ins, LWP) have been applied correctly."
        ],

        full_docs: {
            title: "Payroll Module: Financial Manual",
            groups: [
                {
                    group_title: "1. Payroll Control (List View)",
                    sections: [
                        {
                            label: "Batch Tracking",
                            content: "Each payroll run is assigned a unique Batch ID (#00001) for financial auditing and tracking.",
                            icon: "ShieldCheck"
                        },
                        {
                            label: "Status Lifecycle",
                            content: "Draft: Editable | Posted: Awaiting Approval | Approved: Finalized & Locked.",
                            icon: "UserCheck"
                        },
                        {
                            label: "Financial Overview",
                            content: "Compare 'Gross Amount' (Total Liability) vs 'Net Payable' (Actual Cash Outflow) at a glance.",
                            icon: "Banknote"
                        },
                        {
                            label: "Audit Trail",
                            content: "Displays 'Prepared By' info and timestamps to track who generated each financial record.",
                            icon: "MousePointer2"
                        }
                    ]
                },
                {
                    group_title: "2. Generation Protocol (Form)",
                    sections: [
                        {
                            label: "Salary vs. Bonus",
                            content: "Toggle between standard monthly salary or special disbursements like Festival/KPI bonuses.",
                            icon: "Gift"
                        },
                        {
                            label: "Snapshot Logic",
                            content: "The system takes a 'snapshot' of the current Employee contract (Basic/Allowances) at the moment of generation.",
                            icon: "Calculator"
                        },
                        {
                            label: "Filtering Scope",
                            content: "Run payroll for the entire company, or drill down into specific Offices and Departments.",
                            icon: "Zap"
                        },
                        {
                            label: "Preview & Post",
                            content: "The Preview Modal allows a line-by-line verification of staff earnings before the batch is saved to the database.",
                            icon: "Eye"
                        },
                        {
                            label: "Tax & Deductions",
                            content: "Calculations automatically incorporate 'Tax Status' and 'Salary Stop' flags defined in the Employee module.",
                            icon: "CreditCard"
                        }
                    ]
                }
            ]
        }
    },
    companies: {
        title: "Company Management",
        description: "The primary legal and organizational framework of the HRIS. This module defines the corporate entities, financial identities, and branding assets that govern the employment structure and professional communication across the entire group.",

        tips: [
            "Assign a unique **Company Code** (e.g., HQ-01) for easier filtering in Payroll and Reports.",
            "Upload a high-quality **Logo** to ensure professional branding on generated Pay Slips and Movement Reports.",
            "Correct **Tax ID (TIN)** and **Registration Numbers** are mandatory for legal compliance and auditing.",
            "The **Industry Type** (e.g., Factory vs. Customer) determines how the entity interacts with the Movement and Logistics modules.",
            "Ensure the **Official Email** is accurate, as it serves as the primary contact for automated system notifications.",
            "Setting a company to **'Inactive'** will hide it from new employee registrations but preserve historical records."
        ],

        full_docs: {
            title: "Corporate Identity: Full Guide",
            groups: [
                {
                    group_title: "1. Organizational Inventory (List View)",
                    sections: [
                        {
                            label: "Company Identity",
                            content: "Displays the brand logo, official name, and internal short-code for quick identification.",
                            icon: "Building2"
                        },
                        {
                            label: "Industry Classification",
                            content: "Categorizes the entity (Own Company, Factory, or Customer) to drive specific system behaviors.",
                            icon: "LayoutList"
                        },
                        {
                            label: "Communication Hub",
                            content: "Summary of official email and phone records for rapid contact within the group.",
                            icon: "Smartphone"
                        },
                        {
                            label: "Operational Status",
                            content: "Green (Active) indicates the company is fully operational and available for staff assignment.",
                            icon: "UserCheck"
                        }
                    ]
                },
                {
                    group_title: "2. Entity Configuration (Form)",
                    sections: [
                        {
                            label: "Primary Identity",
                            content: "Defines the legal name and industry type. These are mandatory fields for database integrity.",
                            icon: "ShieldCheck"
                        },
                        {
                            label: "Legal & Tax Data",
                            content: "Stores Registration No, TIN, and Ownership type (Private Ltd, etc.) required for financial audits.",
                            icon: "Lock"
                        },
                        {
                            label: "Visual Branding",
                            content: "Allows logo uploads which are automatically resized for system-wide document headers.",
                            icon: "Zap"
                        },
                        {
                            label: "Global Address",
                            content: "Full physical mapping including State, City, and Zip Code for official correspondence.",
                            icon: "CalendarDays"
                        },
                        {
                            label: "Contact Protocols",
                            content: "Official website and communication lines used in automated HR letterheads.",
                            icon: "InfoIcon"
                        }
                    ]
                }
            ]
        }
    },
    master_data: {
        title: "Master Data Management",
        description: "The central configuration engine for the entire HRIS. This module governs the global parameters and dropdown values—such as locations, transport modes, and visit purposes—ensuring data consistency across all operational workflows.",

        tips: [
            "Master data provides the options seen in dropdowns throughout the system (e.g., the Cities shown in the People module).",
            "Use the **'Unique Code'** field (e.g., BD-01) for standardized reporting and easier data exports.",
            "The **'Parent / Group'** feature allows for hierarchical data, such as linking a specific City to its parent Country.",
            "Deleting a master record is restricted if it is already in use by another module to prevent database corruption.",
            "Use the **'Description'** field to provide context for specialized codes or internal transport categories.",
            "Always select the correct **'Entry Type'** before saving to ensure the record appears in the intended part of the system."
        ],

        full_docs: {
            title: "Master Data: Global Configuration Guide",
            groups: [
                {
                    group_title: "1. Registry Management (List View)",
                    sections: [
                        {
                            label: "Categorized Views",
                            content: "Records are grouped by Type (Country, City, Transport, etc.). Use the icons to quickly identify the category.",
                            icon: "Layers"
                        },
                        {
                            label: "Hierarchical Linking",
                            content: "Displays the parent relationship (e.g., showing which Country a City belongs to) in the Parent/Group column.",
                            icon: "GitBranch"
                        },
                        {
                            label: "Action Protocols",
                            content: "Blue Pencil: Update Name or Code | Red Trash: Remove unused configuration.",
                            icon: "MousePointer2"
                        },
                        {
                            label: "System Codes",
                            content: "Displays the short-hand mono-spaced codes used for rapid data identification and backend processing.",
                            icon: "Hash"
                        }
                    ]
                },
                {
                    group_title: "2. Configuration & Entry (Form)",
                    sections: [
                        {
                            label: "Entry Type Selection",
                            content: "Determines where this data will be used. Changing the type dynamically updates the available parent options.",
                            icon: "Type"
                        },
                        {
                            label: "Unique Identifiers",
                            content: "The Code field must be unique within its category to prevent conflicts during reporting.",
                            icon: "Hash"
                        },
                        {
                            label: "Display Nomenclature",
                            content: "The 'Display Name' is exactly what users will see in dropdown menus across the application.",
                            icon: "InfoIcon"
                        },
                        {
                            label: "Root Assignment",
                            content: "Allows you to build trees (e.g., selecting 'Bangladesh' as the parent for the city 'Dhaka').",
                            icon: "GitBranch"
                        },
                        {
                            label: "Audit Notes",
                            content: "The Description field allows you to store internal notes about why a specific master record was created.",
                            icon: "InfoIcon"
                        }
                    ]
                }
            ]
        }
    },
    security: {
        title: "Access Matrix & Security",
        description: "The core security governance layer of the HRIS. This module manages the granular permission structure for every user role, defining specific 'Action Rights' (View, Create, Edit, Delete) across all functional modules.",

        tips: [
            "Permissions are **Role-Based**; changes here apply instantly to every user assigned to the selected Role.",
            "Use the **'Grant Everything'** toggle only for Super Admin roles to ensure full system visibility.",
            "The **'Module All'** icon (Layers) allows you to quickly toggle all 4 permissions for a single row.",
            "Always follow the **Principle of Least Privilege**: only grant 'Delete' rights to senior-level managers.",
            "Changes must be saved via the **'Save Changes'** button to commit the new security policy to the database.",
            "Ensure 'View' permissions are granted if an employee needs to see a module, even if they cannot edit it."
        ],

        full_docs: {
            title: "Security & Permissions: Master Manual",
            groups: [
                {
                    group_title: "1. Security Governance (Matrix View)",
                    sections: [
                        {
                            label: "Granular Controls",
                            content: "Permissions are divided into 4 keys: View (Read-only), Create (Add New), Edit (Modify), and Delete (Remove).",
                            icon: "ShieldCheck"
                        },
                        {
                            label: "Role Selection",
                            content: "Use the top dropdown to switch between roles (e.g., Admin, HR-Manager) before modifying the grid.",
                            icon: "UserCheck"
                        },
                        {
                            label: "Visual Indicators",
                            content: "Blue Blue Squares: Access Granted. Faded Plus: Access Restricted.",
                            icon: "Zap"
                        },
                        {
                            label: "Bulk Row Toggles",
                            content: "The 'Layers' icon at the end of each row allows for one-click assignment of all module actions.",
                            icon: "Layers"
                        }
                    ]
                },
                {
                    group_title: "2. Permission Logic & Rules",
                    sections: [
                        {
                            label: "Slug Namespace",
                            content: "Permissions follow the format 'module.action' (e.g., 'payroll.approve'). These are absolute system rules.",
                            icon: "Hash"
                        },
                        {
                            label: "Global Revocation",
                            content: "Use 'Revoke Everything' to completely reset a role's access—useful when re-designing security protocols.",
                            icon: "XCircle"
                        },
                        {
                            label: "Audit Protection",
                            content: "The system locks 'Delete' actions by default. Manual granting is required for high-risk operations.",
                            icon: "Lock"
                        },
                        {
                            label: "Real-time Updates",
                            content: "Once saved, users may need to refresh their page to see the updated menu and action buttons.",
                            icon: "Zap"
                        },
                        {
                            label: "Commit Protocol",
                            content: "The 'Save Changes' button performs a database sync, overwriting previous permission snapshots for that role.",
                            icon: "Save"
                        }
                    ]
                }
            ]
        }
    }
};
