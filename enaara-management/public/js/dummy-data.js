/**
 * Project Dummy Data
 * Centralized dummy data for all modules
 * Organization: Enaara Developers
 * Child Organizations: Madison Square Mall Rawalpindi, Madison Square Mall Lahore, Royal Swiss Lahore
 */

const ProjectData = {
    // ============================================
    // DASHBOARD DATA
    // ============================================
    dashboard: {
        // Attendance Chart Data
        attendance: {
            7: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                present: [120, 125, 118, 130, 125, 80, 45],
                absent: [15, 12, 18, 8, 10, 5, 3],
                onLeave: [15, 13, 14, 12, 15, 65, 102]
            },
            14: {
                labels: ['W1 Mon', 'W1 Tue', 'W1 Wed', 'W1 Thu', 'W1 Fri', 'W1 Sat', 'W1 Sun',
                         'W2 Mon', 'W2 Tue', 'W2 Wed', 'W2 Thu', 'W2 Fri', 'W2 Sat', 'W2 Sun'],
                present: [120, 125, 118, 130, 125, 80, 45, 115, 122, 120, 128, 123, 85, 50],
                absent: [15, 12, 18, 8, 10, 5, 3, 18, 15, 20, 9, 12, 6, 4],
                onLeave: [15, 13, 14, 12, 15, 65, 102, 17, 13, 16, 14, 16, 70, 105]
            },
            28: {
                labels: ['D1', 'D2', 'D3', 'D4', 'D5', 'D6', 'D7',
                         'D8', 'D9', 'D10', 'D11', 'D12', 'D13', 'D14',
                         'D15', 'D16', 'D17', 'D18', 'D19', 'D20', 'D21',
                         'D22', 'D23', 'D24', 'D25', 'D26', 'D27', 'D28'],
                present: [120, 125, 118, 130, 125, 80, 45, 115, 122, 120, 128, 123, 85, 50,
                          118, 124, 119, 132, 127, 82, 47, 116, 121, 117, 129, 124, 83, 48],
                absent: [15, 12, 18, 8, 10, 5, 3, 18, 15, 20, 9, 12, 6, 4,
                         16, 13, 19, 9, 11, 6, 3, 17, 14, 21, 10, 13, 7, 4],
                onLeave: [15, 13, 14, 12, 15, 65, 102, 17, 13, 16, 14, 16, 70, 105,
                          16, 14, 15, 13, 16, 68, 100, 18, 15, 17, 15, 17, 72, 108]
            }
        },

        // Department Chart Data
        department: {
            labels: ['', 'Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'],
            datasets: [
                { label: 'IT', data: [50, 75, 85, 70, 90, 65, 88] },
                { label: 'Sales', data: [40, 45, 55, 65, 60, 70, 75] },
                { label: 'HR', data: [30, 90, 95, 85, 100, 88, 92] },
                { label: 'Operations', data: [80, 60, 70, 55, 75, 50, 68] }
            ]
        },

        // Workforce Strength Data
        workforce: {
            totalEmployees: 1000,
            departmentData: [
                [0, 75, 85, 70, 90, 65, 88], // IT
                [0, 45, 55, 65, 60, 70, 75], // Sales
                [0, 90, 95, 85, 100, 88, 92], // HR
                [0, 60, 70, 55, 75, 50, 68]  // Operations
            ]
        },

        // Geofence Map Data
        geofence: {
            center: [33.5651, 73.0169], // Rawalpindi coordinates
            zoom: 13,
            zones: [
                {
                    name: 'Main Office',
                    center: [33.5651, 73.0169],
                    radius: 500,
                    color: '#31d0aa',
                    employees: 850
                },
                {
                    name: 'Branch Office 1',
                    center: [33.5750, 73.0260],
                    radius: 300,
                    color: '#28a745',
                    employees: 100
                },
                {
                    name: 'Branch Office 2',
                    center: [33.5550, 73.0070],
                    radius: 400,
                    color: '#28a745',
                    employees: 0
                }
            ],
            employees: {
                inZone: [
                    { name: 'Ahmed Ali', position: [33.5651, 73.0169], status: 'in-zone' },
                    { name: 'Fatima Khan', position: [33.5655, 73.0175], status: 'in-zone' },
                    { name: 'Hassan Malik', position: [33.5645, 73.0165], status: 'in-zone' },
                    { name: 'Ayesha Sheikh', position: [33.5750, 73.0260], status: 'in-zone' }
                ],
                outZone: [
                    { name: 'Usman Raza', position: [33.5800, 73.0300], status: 'out-zone' },
                    { name: 'Zainab Abbas', position: [33.5500, 73.0100], status: 'out-zone' }
                ],
                vpn: [
                    { name: 'Ali Hassan', position: [33.5700, 73.0200], status: 'vpn-proxy' }
                ]
            }
        }
    },

    // ============================================
    // GEOFENCING DATA
    // ============================================
    geofencing: {
        sampleFences: [
            {
                id: 1,
                name: 'Enaara Tower A',
                address: 'Commercial Area, Rawalpindi, Pakistan',
                lat: 33.5651,
                lng: 73.0169,
                radius: 200,
                radiusUnit: 'meters',
                type: 'hard-lock',
                assignedGroups: ['Site Maintenance', 'Security Team'],
                insideCount: 45,
                outsideCount: 12,
                status: 'active'
            },
            {
                id: 2,
                name: 'Downtown Site',
                address: 'Saddar, Rawalpindi, Pakistan',
                lat: 33.6000,
                lng: 73.0500,
                radius: 150,
                radiusUnit: 'meters',
                type: 'soft-lock',
                assignedGroups: ['Sales Team', 'Field Agents'],
                insideCount: 28,
                outsideCount: 5,
                status: 'active'
            },
            {
                id: 3,
                name: 'Construction Site B',
                address: 'DHA Phase 1, Rawalpindi, Pakistan',
                lat: 33.5500,
                lng: 73.0000,
                radius: 300,
                radiusUnit: 'meters',
                type: 'hard-lock',
                assignedGroups: ['Construction Team'],
                insideCount: 69,
                outsideCount: 0,
                status: 'active'
            }
        ]
    },

    // ============================================
    // SHIFT PLANNER DATA
    // ============================================
    shiftPlanner: {
        employees: [
            { id: '1', name: 'Ahmed Ali' },
            { id: '2', name: 'Zainab Malik' },
            { id: '3', name: 'Bilal Ahmed' },
            { id: '4', name: 'Hira Ali' },
            { id: '5', name: 'Hamza Khan' },
            { id: '6', name: 'Sana Sheikh' },
            { id: '7', name: 'Faisal Raza' },
            { id: '8', name: 'Ayesha Malik' },
            { id: '9', name: 'Usman Ali' },
            { id: '10', name: 'Mariam Khan' }
        ]
    },

    // ============================================
    // LEAVE REQUESTS DATA
    // ============================================
    leaveRequests: {
        sampleLeaveRequests: [
            {
                id: 1,
                employeeName: 'Ahmed Ali',
                employeeId: 'EMP-001',
                department: 'Sales',
                leaveType: 'annual',
                leaveTypeLabel: 'Annual Leave',
                startDate: '2024-02-01',
                endDate: '2024-02-05',
                days: 5,
                reason: 'Family vacation',
                status: 'pending',
                approvalLevel: 'supervisor',
                pendingSince: '2 days ago',
                balance: 25
            },
            {
                id: 2,
                employeeName: 'Zainab Malik',
                employeeId: 'EMP-002',
                department: 'HR',
                leaveType: 'sick',
                leaveTypeLabel: 'Sick Leave',
                startDate: '2024-01-25',
                endDate: '2024-01-26',
                days: 2,
                reason: 'Medical appointment',
                status: 'pending',
                approvalLevel: 'hr',
                pendingSince: '1 day ago',
                balance: 13
            },
            {
                id: 3,
                employeeName: 'Bilal Ahmed',
                employeeId: 'EMP-003',
                department: 'IT',
                leaveType: 'casual',
                leaveTypeLabel: 'Casual Leave',
                startDate: '2024-01-30',
                endDate: '2024-01-30',
                days: 1,
                reason: 'Personal work',
                status: 'approved',
                approvalLevel: 'super-admin',
                pendingSince: '-',
                balance: 8
            },
            {
                id: 4,
                employeeName: 'Hira Ali',
                employeeId: 'EMP-004',
                department: 'Operations',
                leaveType: 'annual',
                leaveTypeLabel: 'Annual Leave',
                startDate: '2024-02-10',
                endDate: '2024-02-15',
                days: 6,
                reason: 'Holiday trip',
                status: 'pending',
                approvalLevel: 'manager',
                pendingSince: '3 days ago',
                balance: 20
            }
        ]
    },

    // ============================================
    // BALANCE TRACKER DATA
    // ============================================
    balanceTracker: {
        sampleBalances: [
            {
                id: 1,
                employeeName: 'Ahmed Ali',
                employeeId: 'EMP-001',
                joinDate: '2020-01-15',
                organization: 'Enaara Developers',
                department: 'Sales',
                annual: { earned: 30, used: 5, remaining: 25 },
                sick: { earned: 15, used: 2, remaining: 13 },
                casual: { earned: 10, used: 2, remaining: 8 }
            },
            {
                id: 2,
                employeeName: 'Zainab Malik',
                employeeId: 'EMP-002',
                joinDate: '2019-03-20',
                organization: 'Madison Square Mall Rawalpindi',
                department: 'HR',
                annual: { earned: 30, used: 8, remaining: 22 },
                sick: { earned: 15, used: 3, remaining: 12 },
                casual: { earned: 10, used: 1, remaining: 9 }
            },
            {
                id: 3,
                employeeName: 'Bilal Ahmed',
                employeeId: 'EMP-003',
                joinDate: '2021-06-10',
                organization: 'Madison Square Mall Lahore',
                department: 'IT',
                annual: { earned: 30, used: 12, remaining: 18 },
                sick: { earned: 15, used: 1, remaining: 14 },
                casual: { earned: 10, used: 4, remaining: 6 }
            },
            {
                id: 4,
                employeeName: 'Hira Ali',
                employeeId: 'EMP-004',
                joinDate: '2020-11-05',
                organization: 'Royal Swiss Lahore',
                department: 'Operations',
                annual: { earned: 30, used: 15, remaining: 15 },
                sick: { earned: 15, used: 5, remaining: 10 },
                casual: { earned: 10, used: 3, remaining: 7 }
            },
            {
                id: 5,
                employeeName: 'Hamza Khan',
                employeeId: 'EMP-005',
                joinDate: '2022-02-14',
                organization: 'Enaara Developers',
                department: 'Finance',
                annual: { earned: 30, used: 3, remaining: 27 },
                sick: { earned: 15, used: 0, remaining: 15 },
                casual: { earned: 10, used: 5, remaining: 5 }
            }
        ]
    },

    // ============================================
    // LEAVE CALENDAR DATA
    // ============================================
    leaveCalendar: {
        departmentalLeaves: [
            // February 2026 - Week 1
            { date: '2026-02-01', department: 'Sales', count: 3, total: 20 },
            { date: '2026-02-01', department: 'HR', count: 1, total: 10 },
            { date: '2026-02-01', department: 'IT', count: 2, total: 15 },
            { date: '2026-02-02', department: 'Operations', count: 2, total: 18 },
            { date: '2026-02-02', department: 'Finance', count: 1, total: 12 },
            { date: '2026-02-05', department: 'Sales', count: 8, total: 20 },
            { date: '2026-02-05', department: 'IT', count: 6, total: 15 },
            { date: '2026-02-05', department: 'Marketing', count: 3, total: 14 },
            { date: '2026-02-06', department: 'HR', count: 2, total: 10 },
            { date: '2026-02-06', department: 'Operations', count: 3, total: 18 },
            { date: '2026-02-07', department: 'Finance', count: 2, total: 12 },
            { date: '2026-02-07', department: 'Sales', count: 4, total: 20 },
            { date: '2026-02-08', department: 'IT', count: 3, total: 15 },
            { date: '2026-02-08', department: 'Operations', count: 2, total: 18 },
            { date: '2026-02-09', department: 'HR', count: 1, total: 10 },
            
            // February 2026 - Week 2
            { date: '2026-02-12', department: 'Operations', count: 7, total: 18 },
            { date: '2026-02-12', department: 'IT', count: 4, total: 15 },
            { date: '2026-02-13', department: 'Sales', count: 5, total: 20 },
            { date: '2026-02-13', department: 'Finance', count: 2, total: 12 },
            { date: '2026-02-14', department: 'Sales', count: 4, total: 20 },
            { date: '2026-02-14', department: 'HR', count: 2, total: 10 },
            { date: '2026-02-15', department: 'Sales', count: 9, total: 20 },
            { date: '2026-02-15', department: 'HR', count: 4, total: 10 },
            { date: '2026-02-16', department: 'IT', count: 5, total: 15 },
            { date: '2026-02-16', department: 'Operations', count: 3, total: 18 }
        ]
    },

    // ============================================
    // ORGANIZATIONS DATA
    // ============================================
    organizations: {
        main: {
            id: 1,
            name: 'Enaara Developers',
            type: 'parent',
            children: [2, 3, 4]
        },
        children: [
            {
                id: 2,
                name: 'Madison Square Mall Rawalpindi',
                type: 'child',
                parentId: 1,
                location: 'Rawalpindi, Pakistan'
            },
            {
                id: 3,
                name: 'Madison Square Mall Lahore',
                type: 'child',
                parentId: 1,
                location: 'Lahore, Pakistan'
            },
            {
                id: 4,
                name: 'Royal Swiss Lahore',
                type: 'child',
                parentId: 1,
                location: 'Lahore, Pakistan'
            }
        ]
    },

    // ============================================
    // COMMON EMPLOYEES DATA
    // ============================================
    employees: {
        common: [
            {
                id: 1,
                name: 'Ahmed Ali',
                employeeId: 'EMP-001',
                department: 'Sales',
                organization: 'Enaara Developers',
                email: 'ahmed.ali@enaara.com',
                phone: '+92-300-1234567',
                position: 'Sales Manager'
            },
            {
                id: 2,
                name: 'Zainab Malik',
                employeeId: 'EMP-002',
                department: 'HR',
                organization: 'Madison Square Mall Rawalpindi',
                email: 'zainab.malik@enaara.com',
                phone: '+92-300-1234568',
                position: 'HR Manager'
            },
            {
                id: 3,
                name: 'Bilal Ahmed',
                employeeId: 'EMP-003',
                department: 'IT',
                organization: 'Madison Square Mall Lahore',
                email: 'bilal.ahmed@enaara.com',
                phone: '+92-300-1234569',
                position: 'IT Manager'
            },
            {
                id: 4,
                name: 'Hira Ali',
                employeeId: 'EMP-004',
                department: 'Operations',
                organization: 'Royal Swiss Lahore',
                email: 'hira.ali@enaara.com',
                phone: '+92-300-1234570',
                position: 'Operations Manager'
            },
            {
                id: 5,
                name: 'Hamza Khan',
                employeeId: 'EMP-005',
                department: 'Finance',
                organization: 'Enaara Developers',
                email: 'hamza.khan@enaara.com',
                phone: '+92-300-1234571',
                position: 'Finance Manager'
            }
        ]
    }
};

