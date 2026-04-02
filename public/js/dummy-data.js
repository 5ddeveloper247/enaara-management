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
            { id: '1', name: 'Shehryar Shahid' },
            { id: '2', name: 'Ahmed Ali' },
            { id: '3', name: 'Bilal Ahmed' },
            { id: '4', name: 'Hira Ali' }
        ]
    },

    // ============================================
    // ROSTER (DUMMY) DATA FOR SHIFT PLANNER
    // ============================================
    roster: {
        departments: [
            { id: 1, name: 'Sales' },
            { id: 2, name: 'IT' }
        ],
        employees: [
            { id: 1, name: 'Shehryar Shahid', departmentId: 1 },
            { id: 2, name: 'Ahmed Ali', departmentId: 1 },
            { id: 3, name: 'Bilal Ahmed', departmentId: 2 },
            { id: 4, name: 'Hira Ali', departmentId: 2 }
        ],
        shifts: [
            // Week example: days 29, 30, 31
            { employeeId: 1, day: 29, shiftType: 'evening', timeStart: '13:00', timeEnd: '21:00', checkIn: '12:55', checkOut: '21:00', floor: 'Ward B • 2nd Floor', lateCheckIn: false },
            { employeeId: 1, day: 30, shiftType: 'night', timeStart: '21:00', timeEnd: '06:00', checkIn: '21:15', checkOut: '06:08', floor: 'Showroom • Ground', lateCheckIn: true },
            { employeeId: 1, day: 31, shiftType: 'morning', timeStart: '09:00', timeEnd: '17:00', checkIn: '08:55', checkOut: '17:00', floor: 'Counter • 1st Floor', lateCheckIn: false },

            { employeeId: 2, day: 30, shiftType: 'morning', timeStart: '09:00', timeEnd: '17:00', checkIn: '09:00', checkOut: '17:00', floor: 'Showroom • Ground', lateCheckIn: false },

            { employeeId: 3, day: 29, shiftType: 'morning', timeStart: '09:00', timeEnd: '17:00', checkIn: '08:55', checkOut: '17:00', floor: 'Dev • 3rd Floor', lateCheckIn: false },
            { employeeId: 3, day: 30, shiftType: 'morning', timeStart: '09:00', timeEnd: '17:00', checkIn: '09:00', checkOut: '17:00', floor: 'IT Block • 1st Floor', lateCheckIn: false },

            { employeeId: 4, day: 29, shiftType: 'evening', timeStart: '13:00', timeEnd: '21:00', checkIn: '12:55', checkOut: '21:00', floor: 'Server Room • Basement', lateCheckIn: false }
        ],
        shiftTemplates: {
            general: {
                timeStart: '09:00',
                timeEnd: '17:00',
                checkInEarly: '08:55',
                checkInLate: '09:10',
                checkOutEarly: '17:00',
                checkOutLate: '17:05'
            }
        }
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
    },

    // ============================================
    // MONTHLY SUMMARY DATA
    // ============================================
    monthlySummary: {
        generateSampleData: function (count = 50) {
            const names = ['AA', 'ZM', 'BA', 'HA', 'HK', 'SS', 'AR', 'KA', 'HM', 'TK'];
            const fullNames = ['Ahmed Ali', 'Zainab Malik', 'Bilal Ahmed', 'Hira Ali', 'Hamza Khan', 'Sana Sheikh', 'Ali Raza', 'Khurram Ali', 'Hina Malik', 'Tariq Khan'];
            const departments = ['Sales', 'HR', 'IT', 'Legal', 'Operations', 'Finance'];
            const floors = ['Ground', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];
            const branches = ['Rawalpindi', 'Lahore', 'Karachi'];

            const data = [];

            for (let i = 0; i < count; i++) {
                const nameIndex = i % names.length;
                const deptIndex = i % departments.length;
                const floorIndex = i % floors.length;
                const branchIndex = i % branches.length;
                const totalDays = 30;
                const present = Math.floor(Math.random() * (28 - 22 + 1)) + 22;
                const absent = totalDays - present - Math.floor(Math.random() * 3);
                const halfDays = Math.floor(Math.random() * 4);
                const annualLeave = Math.floor(Math.random() * 6);
                const sickLeave = Math.floor(Math.random() * 4);
                const casualLeave = Math.floor(Math.random() * 5);
                const lateArrivals = Math.floor(Math.random() * 16);
                const earlyDepartures = Math.floor(Math.random() * 9);
                const isFloor9 = floors[floorIndex] === '9';
                const zone2Verification = isFloor9 ? (Math.floor(Math.random() * 6) === 0 ? 'Pending' : 'Verified') : 'N/A';
                const regularization = Math.floor(Math.random() * 6);

                data.push({
                    id: i + 1,
                    employeeId: 'EMP-' + String(i + 1).padStart(4, '0'),
                    employeeName: fullNames[nameIndex],
                    employeeAvatar: names[nameIndex],
                    department: departments[deptIndex],
                    branch: branches[branchIndex],
                    floor: floors[floorIndex],
                    totalDays: totalDays,
                    present: present,
                    absent: absent,
                    halfDays: halfDays,
                    annualLeave: annualLeave,
                    sickLeave: sickLeave,
                    casualLeave: casualLeave,
                    lateArrivals: lateArrivals,
                    earlyDepartures: earlyDepartures,
                    zone2Verification: zone2Verification,
                    regularization: regularization
                });
            }

            return data;
        }
    },

    // ============================================
    // OVERTIME TRACKER DATA
    // ============================================
    overtime: {
        generateSampleData: function (count = 50) {
            const names = ['AA', 'ZM', 'BA', 'HA', 'HK', 'SS', 'AR', 'KA', 'HM', 'TK'];
            const fullNames = ['Ahmed Ali', 'Zainab Malik', 'Bilal Ahmed', 'Hira Ali', 'Hamza Khan', 'Sana Sheikh', 'Ali Raza', 'Khurram Ali', 'Hina Malik', 'Tariq Khan'];
            const departments = ['Sales', 'HR', 'IT', 'Legal', 'Operations', 'Finance'];
            const floors = ['Ground', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];
            const branches = ['Rawalpindi', 'Lahore', 'Karachi'];
            const organizations = ['Enaara Developers', 'Madison Square Mall Rawalpindi', 'Madison Square Mall Lahore', 'Royal Swiss Lahore'];
            const otCategories = ['In-Office OT', 'Field-Work OT', 'Weekend OT'];
            const statuses = ['pending', 'approved', 'rejected'];

            const data = [];
            const today = new Date();

            for (let i = 0; i < count; i++) {
                const nameIndex = i % names.length;
                const deptIndex = i % departments.length;
                const floorIndex = i % floors.length;
                const branchIndex = i % branches.length;
                const orgIndex = i % organizations.length;

                const isFloor9 = floors[floorIndex] === '9';
                const otCategory = otCategories[Math.floor(Math.random() * otCategories.length)];
                const status = statuses[Math.floor(Math.random() * statuses.length)];

                // Generate date (within last 7 days)
                const daysAgo = Math.floor(Math.random() * 7);
                const otDate = new Date(today);
                otDate.setDate(today.getDate() - daysAgo);
                const dateStr = otDate.toISOString().split('T')[0];

                // Generate shift times
                const shiftEndHour = 18; // 6 PM
                const shiftEndMinute = 0;
                const actualPunchOutHour = shiftEndHour + Math.floor(Math.random() * 4) + 1; // 7 PM to 10 PM
                const actualPunchOutMinute = Math.floor(Math.random() * 60);

                // Calculate OT hours
                const otHours = (actualPunchOutHour - shiftEndHour) + (actualPunchOutMinute / 60);
                const otHoursFormatted = otHours.toFixed(1);

                // Zone-2 verification for Floor 9
                const zone2Verified = isFloor9 ? (Math.floor(Math.random() * 10) < 8 ? true : false) : null;

                // Geofence status (for Field-Work OT)
                const isFieldWork = otCategory === 'Field-Work OT';
                const geofenceStatus = isFieldWork ? (Math.floor(Math.random() * 10) < 7 ? 'in-zone' : 'out-of-zone') : null;

                // Verification status
                let verificationStatus = 'Not Required';
                if (isFloor9 && zone2Verified) {
                    verificationStatus = 'Biometric Verified';
                } else if (isFloor9 && !zone2Verified) {
                    verificationStatus = 'Pending Verification';
                } else if (isFieldWork && geofenceStatus === 'in-zone') {
                    verificationStatus = 'Geofence Verified';
                } else if (isFieldWork && geofenceStatus === 'out-of-zone') {
                    verificationStatus = 'Location Mismatch';
                }

                data.push({
                    id: i + 1,
                    employeeId: 'EMP-' + String(i + 1).padStart(4, '0'),
                    employeeName: fullNames[nameIndex],
                    employeeAvatar: names[nameIndex],
                    department: departments[deptIndex],
                    floor: floors[floorIndex],
                    branch: branches[branchIndex],
                    organization: organizations[orgIndex],
                    date: dateStr,
                    shiftEnd: `${String(shiftEndHour).padStart(2, '0')}:${String(shiftEndMinute).padStart(2, '0')}`,
                    actualPunchOut: `${String(actualPunchOutHour).padStart(2, '0')}:${String(actualPunchOutMinute).padStart(2, '0')}`,
                    otHours: parseFloat(otHoursFormatted),
                    otCategory: otCategory,
                    status: status,
                    zone2Verified: zone2Verified,
                    geofenceStatus: geofenceStatus,
                    verificationStatus: verificationStatus,
                    hasEvidence: Math.floor(Math.random() * 10) < 7
                });
            }

            return data;
        }
    },

    // ============================================
    // AUDIT TRAILS DATA
    // ============================================
    auditTrails: {
        generateSampleData: function (count = 100) {
            const users = [
                { name: 'Ahmed Ali', role: 'Admin', avatar: 'AA' },
                { name: 'Zainab Malik', role: 'HR Manager', avatar: 'ZM' },
                { name: 'Bilal Ahmed', role: 'Admin', avatar: 'BA' },
                { name: 'Hira Ali', role: 'Manager', avatar: 'HA' },
                { name: 'Hamza Khan', role: 'Admin', avatar: 'HK' },
                { name: 'Sana Sheikh', role: 'Super Admin', avatar: 'SS' }
            ];

            const categories = ['Leave', 'Geofence', 'Shift', 'Security', 'Employee', 'System'];
            const severities = ['critical', 'warning', 'info', 'success'];
            const organizations = ['Enaara Developers', 'Madison Square Mall Rawalpindi', 'Madison Square Mall Lahore', 'Royal Swiss Lahore'];
            const branches = ['Rawalpindi', 'Lahore', 'Karachi'];
            const devices = [
                'Chrome on Windows',
                'Safari on macOS',
                'Firefox on Linux',
                'Mobile Chrome',
                'Edge on Windows'
            ];

            const actions = {
                'Leave': [
                    { desc: 'Approved leave request for EMP-0045', severity: 'success', hasChanges: true, changes: [{ field: 'Status', before: 'Pending', after: 'Approved' }] },
                    { desc: 'Rejected leave request for EMP-0023', severity: 'warning', hasChanges: true, changes: [{ field: 'Status', before: 'Pending', after: 'Rejected' }, { field: 'Reason', before: '', after: 'Insufficient balance' }] },
                    { desc: 'Created new leave request', severity: 'info', hasChanges: false },
                    { desc: 'Updated leave balance for EMP-0012', severity: 'info', hasChanges: true, changes: [{ field: 'Annual Leave', before: '25', after: '23' }] }
                ],
                'Geofence': [
                    { desc: 'Modified geofence radius for Main Office', severity: 'info', hasChanges: true, changes: [{ field: 'Radius', before: '200m', after: '250m' }] },
                    { desc: 'Created new geofence zone', severity: 'info', hasChanges: false },
                    { desc: 'Deleted geofence zone: Branch Office 2', severity: 'warning', hasChanges: false },
                    { desc: 'Unauthorized access attempt to Floor 9', severity: 'critical', hasChanges: false, context: 'Employee attempted to access restricted area without proper clearance.' }
                ],
                'Shift': [
                    { desc: 'Assigned shift to employee EMP-0034', severity: 'info', hasChanges: false },
                    { desc: 'Modified shift schedule for Sales Team', severity: 'info', hasChanges: true, changes: [{ field: 'Start Time', before: '09:00', after: '08:30' }, { field: 'End Time', before: '18:00', after: '17:30' }] },
                    { desc: 'Cancelled shift assignment', severity: 'warning', hasChanges: false }
                ],
                'Security': [
                    { desc: 'Changed user password', severity: 'info', hasChanges: false },
                    { desc: 'Granted admin access to user', severity: 'warning', hasChanges: true, changes: [{ field: 'Role', before: 'User', after: 'Admin' }] },
                    { desc: 'Revoked access permissions', severity: 'warning', hasChanges: true, changes: [{ field: 'Status', before: 'Active', after: 'Suspended' }] },
                    { desc: 'Failed login attempt from unauthorized IP', severity: 'critical', hasChanges: false, context: 'Multiple failed login attempts detected from IP: 192.168.1.100' },
                    { desc: 'Unauthorized 9th floor access attempt', severity: 'critical', hasChanges: false, context: 'Employee without Floor 9 clearance attempted to access restricted area.' }
                ],
                'Employee': [
                    { desc: 'Created new employee profile', severity: 'info', hasChanges: false },
                    { desc: 'Updated employee department', severity: 'info', hasChanges: true, changes: [{ field: 'Department', before: 'Sales', after: 'Marketing' }] },
                    { desc: 'Modified employee biometric ID', severity: 'warning', hasChanges: true, changes: [{ field: 'Biometric ID', before: 'BIO-001234', after: 'BIO-001567' }] },
                    { desc: 'Deactivated employee account', severity: 'warning', hasChanges: true, changes: [{ field: 'Status', before: 'Active', after: 'Inactive' }] }
                ],
                'System': [
                    { desc: 'System configuration updated', severity: 'info', hasChanges: true, changes: [{ field: 'OT Threshold', before: '2 hours', after: '2.5 hours' }] },
                    { desc: 'Database backup completed', severity: 'success', hasChanges: false },
                    { desc: 'System maintenance performed', severity: 'info', hasChanges: false }
                ]
            };

            const data = [];
            const today = new Date();

            for (let i = 0; i < count; i++) {
                const userIndex = i % users.length;
                const category = categories[Math.floor(Math.random() * categories.length)];
                const categoryActions = actions[category];
                const action = categoryActions[Math.floor(Math.random() * categoryActions.length)];
                const orgIndex = i % organizations.length;
                const branchIndex = i % branches.length;

                // Generate timestamp (within last 30 days, more recent entries more likely)
                const daysAgo = Math.floor(Math.random() * 30);
                const hoursAgo = Math.floor(Math.random() * 24);
                const minutesAgo = Math.floor(Math.random() * 60);
                const timestamp = new Date(today);
                timestamp.setDate(today.getDate() - daysAgo);
                timestamp.setHours(today.getHours() - hoursAgo);
                timestamp.setMinutes(today.getMinutes() - minutesAgo);

                // Generate IP address
                const ipAddress = `192.168.${Math.floor(Math.random() * 255)}.${Math.floor(Math.random() * 255)}`;

                // Generate device
                const device = devices[Math.floor(Math.random() * devices.length)];

                data.push({
                    id: i + 1,
                    timestamp: timestamp.toISOString(),
                    user: users[userIndex],
                    category: category,
                    description: action.desc,
                    severity: action.severity,
                    ipAddress: ipAddress,
                    device: device,
                    organization: organizations[orgIndex],
                    branch: branches[branchIndex],
                    hasChanges: action.hasChanges || false,
                    changes: action.changes || [],
                    context: action.context || null
                });
            }

            // Sort by timestamp (newest first)
            data.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));

            return data;
        }
    },

    // ============================================
    // POLICIES DATA
    // ============================================
    policies: {
        generateSampleData: function (count = 30) {
            const categories = ['Leave Policy', 'Attendance Grace Period', 'Geofencing Rules', 'Shift Rota Protocols', 'Security Policy', 'HR Policy'];
            const statuses = ['active', 'draft', 'archived'];
            const organizations = ['Enaara Developers', 'Madison Square Mall Rawalpindi', 'Madison Square Mall Lahore', 'Royal Swiss Lahore'];
            const branches = ['Rawalpindi', 'Lahore', 'Karachi'];
            const floors = ['Ground', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];
            const applicableToTypes = ['global', 'organization', 'branch', 'floor'];

            const policyTemplates = {
                'Leave Policy': [
                    'Annual Leave Policy',
                    'Sick Leave Guidelines',
                    'Casual Leave Rules',
                    'Maternity Leave Policy',
                    'Compensatory Off Policy'
                ],
                'Attendance Grace Period': [
                    'Late Arrival Grace Period',
                    'Early Departure Policy',
                    'Break Time Regulations',
                    'Overtime Attendance Rules'
                ],
                'Geofencing Rules': [
                    'Office Geofence Policy',
                    'Field Work Geofence Rules',
                    'Remote Work Geofence Guidelines',
                    'Multi-Location Geofence Policy'
                ],
                'Shift Rota Protocols': [
                    'Shift Scheduling Policy',
                    'Shift Swap Guidelines',
                    'Night Shift Regulations',
                    'Weekend Shift Policy'
                ],
                'Security Policy': [
                    'Access Control Policy',
                    'Floor 9 Security Protocol',
                    'Visitor Management Policy',
                    'Data Security Guidelines'
                ],
                'HR Policy': [
                    'Employee Onboarding Policy',
                    'Performance Review Policy',
                    'Disciplinary Action Policy',
                    'Termination Policy'
                ]
            };

            const data = [];
            const today = new Date();

            for (let i = 0; i < count; i++) {
                const category = categories[Math.floor(Math.random() * categories.length)];
                const templates = policyTemplates[category];
                const title = templates[Math.floor(Math.random() * templates.length)];
                const status = statuses[Math.floor(Math.random() * statuses.length)];
                const applicableTo = applicableToTypes[Math.floor(Math.random() * applicableToTypes.length)];

                // Generate dates
                const effectiveDate = new Date(today);
                effectiveDate.setDate(today.getDate() - Math.floor(Math.random() * 365));

                const lastUpdated = new Date(effectiveDate);
                lastUpdated.setDate(effectiveDate.getDate() + Math.floor(Math.random() * 30));

                // Determine applicable details based on type
                let applicableDetails = '';
                let organization = null;
                let branch = null;
                let floor = null;

                if (applicableTo === 'global') {
                    applicableDetails = 'Global (All Organizations)';
                } else if (applicableTo === 'organization') {
                    organization = organizations[Math.floor(Math.random() * organizations.length)];
                    applicableDetails = organization;
                } else if (applicableTo === 'branch') {
                    branch = branches[Math.floor(Math.random() * branches.length)];
                    applicableDetails = `${branch} Branch`;
                } else if (applicableTo === 'floor') {
                    floor = floors[Math.floor(Math.random() * floors.length)];
                    applicableDetails = `Floor ${floor}`;
                }

                // Generate description
                const descriptions = {
                    'Leave Policy': 'This policy outlines the rules and regulations for employee leave requests, including annual leave, sick leave, and other leave types.',
                    'Attendance Grace Period': 'Defines the grace period allowed for late arrivals and early departures without penalty.',
                    'Geofencing Rules': 'Establishes guidelines for geofence-based attendance tracking and location verification.',
                    'Shift Rota Protocols': 'Defines the procedures for shift scheduling, swaps, and management.',
                    'Security Policy': 'Outlines security protocols, access control measures, and safety guidelines.',
                    'HR Policy': 'Covers human resources policies including onboarding, performance reviews, and disciplinary actions.'
                };

                // Random document availability
                const hasDocument = Math.floor(Math.random() * 10) < 7;
                const documentName = hasDocument ? `${title.replace(/\s+/g, '_')}.pdf` : null;
                const documentSize = hasDocument ? `${(Math.random() * 5 + 1).toFixed(2)} MB` : null;

                data.push({
                    id: i + 1,
                    title: title,
                    category: category,
                    status: status,
                    effectiveDate: effectiveDate.toISOString().split('T')[0],
                    lastUpdated: lastUpdated.toISOString(),
                    applicableTo: applicableTo,
                    applicableDetails: applicableDetails,
                    organization: organization,
                    branch: branch,
                    floor: floor,
                    description: descriptions[category] || 'Policy description not available.',
                    hasDocument: hasDocument,
                    documentName: documentName,
                    documentSize: documentSize
                });
            }

            // Sort by last updated (newest first)
            data.sort((a, b) => new Date(b.lastUpdated) - new Date(a.lastUpdated));

            return data;
        }
    },

    // ============================================
    // WORKFLOWS DATA
    // ============================================
    workflows: {
        generateSampleData: function (count = 20) {
            const requestTypes = ['Leave', 'Overtime', 'Regularization', 'Shift'];
            const organizations = ['Enaara Developers', 'Madison Square Mall Rawalpindi', 'Madison Square Mall Lahore', 'Royal Swiss Lahore'];
            const branches = ['Rawalpindi', 'Lahore', 'Karachi'];
            const approverRoles = ['Department Head', 'HR Manager', 'Super Admin', 'Manager', 'Supervisor'];
            const statuses = ['active', 'inactive'];

            const workflowTemplates = {
                'Leave': [
                    { name: 'Standard Leave Approval', levels: ['Supervisor', 'HR Manager', 'Super Admin'] },
                    { name: 'Quick Leave Approval', levels: ['Supervisor', 'HR Manager'] },
                    { name: 'Extended Leave Approval', levels: ['Department Head', 'HR Manager', 'Super Admin'] }
                ],
                'Overtime': [
                    { name: 'Overtime Approval Workflow', levels: ['Supervisor', 'HR Manager'] },
                    { name: 'Field Work OT Approval', levels: ['Manager', 'HR Manager', 'Super Admin'] }
                ],
                'Regularization': [
                    { name: 'Regularization Request Workflow', levels: ['Supervisor', 'HR Manager'] },
                    { name: 'Missed Punch Regularization', levels: ['HR Manager'] }
                ],
                'Shift': [
                    { name: 'Shift Change Approval', levels: ['Supervisor', 'HR Manager'] },
                    { name: 'Shift Swap Workflow', levels: ['Supervisor'] }
                ]
            };

            const data = [];

            for (let i = 0; i < count; i++) {
                const requestType = requestTypes[Math.floor(Math.random() * requestTypes.length)];
                const templates = workflowTemplates[requestType];
                const template = templates[Math.floor(Math.random() * templates.length)];
                const status = statuses[Math.floor(Math.random() * statuses.length)];

                // Organization assignment
                const isGlobal = Math.random() < 0.3;
                const organization = isGlobal ? 'Global' : organizations[Math.floor(Math.random() * organizations.length)];
                const branch = !isGlobal && Math.random() < 0.5 ? branches[Math.floor(Math.random() * branches.length)] : null;

                // Build approval levels
                const approvalLevels = template.levels.map((role, index) => ({
                    level: index + 1,
                    role: role,
                    approverType: role.toLowerCase().replace(/\s+/g, '-')
                }));

                // SLA settings
                const slaHours = [24, 48, 72][Math.floor(Math.random() * 3)];
                const escalateTo = Math.random() < 0.6 ? ['HR Manager', 'Super Admin', 'Next Level'][Math.floor(Math.random() * 3)] : null;

                data.push({
                    id: i + 1,
                    name: template.name,
                    requestType: requestType,
                    status: status,
                    organization: organization,
                    branch: branch,
                    approvalLevels: approvalLevels,
                    slaHours: slaHours,
                    escalateTo: escalateTo,
                    createdAt: new Date(Date.now() - Math.random() * 365 * 24 * 60 * 60 * 1000).toISOString()
                });
            }

            return data;
        }
    },
    rolesPermissions: {
        // Organizational hierarchy: Owner → CEO → COO → GM → Team Lead
        getRolesHierarchy: function () {
            return {
                id: 'owner',
                text: 'Owner',
                level: 1,
                icon: 'bi-person-badge',
                children: [
                    {
                        id: 'ceo',
                        text: 'CEO',
                        level: 2,
                        icon: 'bi-person-badge',
                        children: [
                            {
                                id: 'coo',
                                text: 'COO',
                                level: 3,
                                icon: 'bi-person-badge',
                                children: [
                                    {
                                        id: 'gm',
                                        text: 'General Manager',
                                        level: 4,
                                        icon: 'bi-person-badge',
                                        children: [
                                            {
                                                id: 'team-lead',
                                                text: 'Team Lead',
                                                level: 5,
                                                icon: 'bi-person-badge',
                                                children: []
                                            }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ]
            };
        },

        getRolePermissions: function (roleId) {
            const permissions = {
                owner: {
                    name: 'Owner',
                    level: 1,
                    permissions: {
                        'Dashboard': { view: true, edit: true, delete: true, approve: true, inherited: false },
                        'Employees': { view: true, edit: true, delete: true, approve: true, inherited: false },
                        'Departments': { view: true, edit: true, delete: true, approve: true, inherited: false },
                        'Organizations': { view: true, edit: true, delete: true, approve: true, inherited: false },
                        'Daily Logs': { view: true, edit: true, delete: true, approve: true, inherited: false },
                        'Leave Requests': { view: true, edit: true, delete: true, approve: true, inherited: false },
                        'Overtime': { view: true, edit: true, delete: true, approve: true, inherited: false },
                        'Regularization': { view: true, edit: true, delete: true, approve: true, inherited: false },
                        'Shift Planner': { view: true, edit: true, delete: true, approve: true, inherited: false },
                        'Geofencing': { view: true, edit: true, delete: true, approve: true, inherited: false },
                        'Reports': { view: true, edit: true, delete: true, approve: true, inherited: false },
                        'Settings': { view: true, edit: true, delete: true, approve: true, inherited: false },
                        'Roles & Permissions': { view: true, edit: true, delete: true, approve: true, inherited: false }
                    },
                    dataScope: { organization: 'all', department: 'all', floor: 'all', employee: 'all' },
                    users: [
                        { id: 'owner-1', name: 'James Carter', email: 'james.carter@enaara.com', department: 'Executive', status: 'Active', avatar: 'JC' },
                        { id: 'owner-2', name: 'Sophia Bennett', email: 'sophia.bennett@enaara.com', department: 'Executive', status: 'Active', avatar: 'SB' },
                        { id: 'owner-3', name: 'William Hayes', email: 'william.hayes@enaara.com', department: 'Executive', status: 'Active', avatar: 'WH' }
                    ]
                },

                ceo: {
                    name: 'CEO',
                    level: 2,
                    permissions: {
                        'Dashboard': { view: true, edit: true, delete: false, approve: true, inherited: false },
                        'Employees': { view: true, edit: true, delete: false, approve: true, inherited: false },
                        'Departments': { view: true, edit: true, delete: false, approve: true, inherited: false },
                        'Organizations': { view: true, edit: false, delete: false, approve: false, inherited: false },
                        'Daily Logs': { view: true, edit: false, delete: false, approve: true, inherited: false },
                        'Leave Requests': { view: true, edit: false, delete: false, approve: true, inherited: false },
                        'Overtime': { view: true, edit: false, delete: false, approve: true, inherited: false },
                        'Regularization': { view: true, edit: false, delete: false, approve: true, inherited: false },
                        'Shift Planner': { view: true, edit: true, delete: false, approve: true, inherited: false },
                        'Geofencing': { view: true, edit: false, delete: false, approve: false, inherited: false },
                        'Reports': { view: true, edit: true, delete: false, approve: false, inherited: false },
                        'Settings': { view: true, edit: false, delete: false, approve: false, inherited: false },
                        'Roles & Permissions': { view: false, edit: false, delete: false, approve: false, inherited: false }
                    },
                    dataScope: { organization: 'all', department: 'all', floor: 'all', employee: 'all' },
                    users: [
                        { id: 'ceo-1', name: 'Daniel Morgan', email: 'daniel.morgan@enaara.com', department: 'Executive', status: 'Active', avatar: 'DM' },
                        { id: 'ceo-2', name: 'Rachel Collins', email: 'rachel.collins@enaara.com', department: 'Executive', status: 'Active', avatar: 'RC' }
                    ]
                },

                coo: {
                    name: 'COO',
                    level: 3,
                    permissions: {
                        'Dashboard': { view: true, edit: true, delete: false, approve: true, inherited: true },
                        'Employees': { view: true, edit: true, delete: false, approve: true, inherited: true },
                        'Departments': { view: true, edit: true, delete: false, approve: true, inherited: true },
                        'Organizations': { view: true, edit: false, delete: false, approve: false, inherited: true },
                        'Daily Logs': { view: true, edit: false, delete: false, approve: true, inherited: true },
                        'Leave Requests': { view: true, edit: false, delete: false, approve: true, inherited: true },
                        'Overtime': { view: true, edit: false, delete: false, approve: true, inherited: true },
                        'Regularization': { view: true, edit: false, delete: false, approve: true, inherited: true },
                        'Shift Planner': { view: true, edit: true, delete: false, approve: true, inherited: true },
                        'Geofencing': { view: true, edit: false, delete: false, approve: false, inherited: true },
                        'Reports': { view: true, edit: true, delete: false, approve: false, inherited: true },
                        'Settings': { view: false, edit: false, delete: false, approve: false, inherited: false },
                        'Roles & Permissions': { view: false, edit: false, delete: false, approve: false, inherited: false }
                    },
                    dataScope: { organization: 'specific', department: 'all', floor: 'all', employee: 'all' },
                    users: [
                        { id: 'coo-1', name: 'Ethan Clarke', email: 'ethan.clarke@enaara.com', department: 'Operations', status: 'Active', avatar: 'EC' },
                        { id: 'coo-2', name: 'Natalie Brooks', email: 'natalie.brooks@enaara.com', department: 'Operations', status: 'Active', avatar: 'NB' },
                        { id: 'coo-3', name: 'Marcus Webb', email: 'marcus.webb@enaara.com', department: 'Operations', status: 'Active', avatar: 'MW' },
                        { id: 'coo-4', name: 'Priya Nair', email: 'priya.nair@enaara.com', department: 'Operations', status: 'Inactive', avatar: 'PN' }
                    ]
                },

                gm: {
                    name: 'General Manager',
                    level: 4,
                    permissions: {
                        'Dashboard': { view: true, edit: false, delete: false, approve: true, inherited: true },
                        'Employees': { view: true, edit: true, delete: false, approve: true, inherited: true },
                        'Departments': { view: true, edit: false, delete: false, approve: false, inherited: true },
                        'Organizations': { view: false, edit: false, delete: false, approve: false, inherited: false },
                        'Daily Logs': { view: true, edit: false, delete: false, approve: true, inherited: true },
                        'Leave Requests': { view: true, edit: false, delete: false, approve: true, inherited: true },
                        'Overtime': { view: true, edit: false, delete: false, approve: true, inherited: true },
                        'Regularization': { view: true, edit: false, delete: false, approve: true, inherited: true },
                        'Shift Planner': { view: true, edit: true, delete: false, approve: true, inherited: true },
                        'Geofencing': { view: true, edit: false, delete: false, approve: false, inherited: true },
                        'Reports': { view: true, edit: false, delete: false, approve: false, inherited: true },
                        'Settings': { view: false, edit: false, delete: false, approve: false, inherited: false },
                        'Roles & Permissions': { view: false, edit: false, delete: false, approve: false, inherited: false }
                    },
                    dataScope: { organization: 'own', department: 'all', floor: 'all', employee: 'all' },
                    users: [
                        { id: 'gm-1', name: 'Oliver Grant', email: 'oliver.grant@enaara.com', department: 'IT', status: 'Active', avatar: 'OG' },
                        { id: 'gm-2', name: 'Isabella Fox', email: 'isabella.fox@enaara.com', department: 'HR', status: 'Active', avatar: 'IF' },
                        { id: 'gm-3', name: 'Lucas Stone', email: 'lucas.stone@enaara.com', department: 'Sales', status: 'Active', avatar: 'LS' },
                        { id: 'gm-4', name: 'Amelia Cross', email: 'amelia.cross@enaara.com', department: 'Finance', status: 'Active', avatar: 'AC' },
                        { id: 'gm-5', name: 'Henry Park', email: 'henry.park@enaara.com', department: 'Operations', status: 'Inactive', avatar: 'HP' },
                        { id: 'gm-6', name: 'Charlotte Reed', email: 'charlotte.reed@enaara.com', department: 'IT', status: 'Active', avatar: 'CR' },
                        { id: 'gm-7', name: 'Liam Foster', email: 'liam.foster@enaara.com', department: 'HR', status: 'Active', avatar: 'LF' },
                        { id: 'gm-8', name: 'Zoe Mitchell', email: 'zoe.mitchell@enaara.com', department: 'Sales', status: 'Active', avatar: 'ZM' }
                    ]
                },

                'team-lead': {
                    name: 'Team Lead',
                    level: 5,
                    permissions: {
                        'Dashboard': { view: true, edit: false, delete: false, approve: false, inherited: true },
                        'Employees': { view: true, edit: false, delete: false, approve: false, inherited: true },
                        'Departments': { view: false, edit: false, delete: false, approve: false, inherited: false },
                        'Organizations': { view: false, edit: false, delete: false, approve: false, inherited: false },
                        'Daily Logs': { view: true, edit: false, delete: false, approve: true, inherited: true },
                        'Leave Requests': { view: true, edit: false, delete: false, approve: true, inherited: true },
                        'Overtime': { view: true, edit: false, delete: false, approve: true, inherited: true },
                        'Regularization': { view: true, edit: false, delete: false, approve: true, inherited: true },
                        'Shift Planner': { view: true, edit: true, delete: false, approve: false, inherited: true },
                        'Geofencing': { view: false, edit: false, delete: false, approve: false, inherited: false },
                        'Reports': { view: true, edit: false, delete: false, approve: false, inherited: true },
                        'Settings': { view: false, edit: false, delete: false, approve: false, inherited: false },
                        'Roles & Permissions': { view: false, edit: false, delete: false, approve: false, inherited: false }
                    },
                    dataScope: { organization: 'own', department: 'own', floor: 'own', employee: 'team' },
                    users: [
                        { id: 'tl-1', name: 'Aaron Hughes', email: 'aaron.hughes@enaara.com', department: 'IT', status: 'Active', avatar: 'AH' },
                        { id: 'tl-2', name: 'Bella Turner', email: 'bella.turner@enaara.com', department: 'HR', status: 'Active', avatar: 'BT' },
                        { id: 'tl-3', name: 'Carlos Diaz', email: 'carlos.diaz@enaara.com', department: 'Sales', status: 'Active', avatar: 'CD' },
                        { id: 'tl-4', name: 'Diana Patel', email: 'diana.patel@enaara.com', department: 'Finance', status: 'Active', avatar: 'DP' },
                        { id: 'tl-5', name: 'Evan Shaw', email: 'evan.shaw@enaara.com', department: 'Operations', status: 'Inactive', avatar: 'ES' },
                        { id: 'tl-6', name: 'Fiona Walsh', email: 'fiona.walsh@enaara.com', department: 'IT', status: 'Active', avatar: 'FW' },
                        { id: 'tl-7', name: 'George Kim', email: 'george.kim@enaara.com', department: 'HR', status: 'Active', avatar: 'GK' },
                        { id: 'tl-8', name: 'Hannah Lee', email: 'hannah.lee@enaara.com', department: 'Sales', status: 'Active', avatar: 'HL' },
                        { id: 'tl-9', name: 'Ian Cooper', email: 'ian.cooper@enaara.com', department: 'Finance', status: 'Active', avatar: 'IC' },
                        { id: 'tl-10', name: 'Julia Simmons', email: 'julia.simmons@enaara.com', department: 'Operations', status: 'Active', avatar: 'JS' },
                        { id: 'tl-11', name: 'Kyle Nguyen', email: 'kyle.nguyen@enaara.com', department: 'IT', status: 'Active', avatar: 'KN' },
                        { id: 'tl-12', name: 'Laura Price', email: 'laura.price@enaara.com', department: 'HR', status: 'Inactive', avatar: 'LP' },
                        { id: 'tl-13', name: 'Mason Bell', email: 'mason.bell@enaara.com', department: 'Sales', status: 'Active', avatar: 'MB' },
                        { id: 'tl-14', name: 'Nina Russo', email: 'nina.russo@enaara.com', department: 'Finance', status: 'Active', avatar: 'NR' },
                        { id: 'tl-15', name: 'Oscar Fleming', email: 'oscar.fleming@enaara.com', department: 'Operations', status: 'Active', avatar: 'OF' }
                    ]
                }
            };

            return permissions[roleId] || null;
        },

        // kept for compatibility — no longer called internally
        generateUsersForRole: function (roleId, count) {
            return [];
        },

        getInitials: function (name) {
            return name.split(' ').map(n => n[0]).join('').toUpperCase();
        }
    },

    // ============================================
    // ORGANIZATIONS DATA
    // ============================================
    organizations: {
        generateSampleData: function () {
            return [
                {
                    id: 1,
                    name: 'Enaara Developers',
                    registrationNumber: 'REG-2024-001',
                    logo: '',
                    logoPlaceholder: 'EC',
                    industry: 'Construction & Real Estate',
                    headcount: 450,
                    departments: 12,
                    floors: 10,
                    floorsInfo: 'Ground, 1-9, Corporate Office',
                    adminName: 'Ahmed Ali',
                    adminEmail: 'ahmed.ali@enaara.com',
                    adminAvatar: 'AA',
                    adminRole: 'Admin / HR Manager',
                    adminStatus: 'Active',
                    address: '123 Business District, Dubai, UAE',
                    website: 'www.enaara.com',
                    timezone: 'Asia/Dubai (UTC+4)',
                    workWeek: 'Sunday - Thursday',
                    attendanceRadius: '100',
                    attendanceRadiusUnit: 'meters',
                    authMethod: 'Email/Password',
                    ssoProvider: '',
                    devicesCount: 8,
                    subscriptionStatus: 'Active',
                    plan: 'Enterprise',
                    expiryDate: 'December 31, 2024'
                },
                {
                    id: 2,
                    name: 'MSR Group',
                    registrationNumber: 'REG-2024-002',
                    logo: '',
                    logoPlaceholder: 'MSR',
                    industry: 'Property Development',
                    headcount: 285,
                    departments: 8,
                    floors: 5,
                    floorsInfo: 'Ground, 1-4',
                    adminName: 'Zainab Malik',
                    adminEmail: 'zainab.malik@msrgroup.com',
                    adminAvatar: 'ZM',
                    adminRole: 'Admin / HR Manager',
                    adminStatus: 'Active',
                    address: '456 Commercial Avenue, Riyadh, Saudi Arabia',
                    website: 'www.msrgroup.com',
                    timezone: 'Asia/Riyadh (UTC+3)',
                    workWeek: 'Sunday - Thursday',
                    attendanceRadius: '150',
                    attendanceRadiusUnit: 'meters',
                    authMethod: 'SSO',
                    ssoProvider: 'Azure AD',
                    devicesCount: 5,
                    subscriptionStatus: 'Active',
                    plan: 'Professional',
                    expiryDate: 'November 30, 2024'
                },
                {
                    id: 3,
                    name: 'MSL',
                    registrationNumber: 'REG-2024-003',
                    logo: '',
                    logoPlaceholder: 'SW',
                    industry: 'Infrastructure',
                    headcount: 120,
                    departments: 5,
                    floors: 3,
                    floorsInfo: 'Ground, 1-2',
                    adminName: 'Bilal Ahmed',
                    adminEmail: 'bilal.ahmed@swissbuilders.com',
                    adminAvatar: 'BA',
                    adminRole: 'Admin / HR Manager',
                    adminStatus: 'Pending',
                    address: '789 Industrial Zone, Karachi, Pakistan',
                    website: 'www.swissbuilders.com',
                    timezone: 'Asia/Karachi (UTC+5)',
                    workWeek: 'Monday - Friday',
                    attendanceRadius: '200',
                    attendanceRadiusUnit: 'meters',
                    authMethod: 'Email/Password',
                    ssoProvider: '',
                    devicesCount: 3,
                    subscriptionStatus: 'Pending',
                    plan: 'Basic',
                    expiryDate: 'October 31, 2024'
                },
            ];
        }
    }
};

