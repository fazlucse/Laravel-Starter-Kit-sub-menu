export const HELP_DOCS = {
    users: {
        title: "User Management Manual",
        // Combine them into a "Groups" array
        full_docs: {
            title: "Complete User Guide",
            groups: [
                {
                    group_title: "List & Table Guidelines",
                    sections: [
                        { label: "Action Column", content: "Pencil to Edit, Trash to Delete.", icon: "MousePointer2" },
                        { label: "Mobile Badge", content: "Green = Mobile Allowed. Red = Restricted.", icon: "Smartphone" },
                        { label: "Status", content: "Active (Green) vs Inactive (Red).", icon: "UserCheck" },
                        { label: "Access Days", content: "Authorized working days in the system.", icon: "CalendarDays" }
                    ]
                },
                {
                    group_title: "Form & Entry Guidelines",
                    sections: [
                        { label: "Smart Link", content: "Auto-fill profile data from Employee records.", icon: "Zap" },
                        { label: "Passwords", content: "Leave blank on edit to keep existing password.", icon: "Lock" },
                        { label: "Toggle Days", content: "Quickly select the entire work week.", icon: "CheckSquare" },
                        { label: "User Roles", content: "Admin, Manager, or User permissions.", icon: "ShieldCheck" }
                    ]
                }
            ]
        }
    }
};
