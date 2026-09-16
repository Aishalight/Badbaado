/* ================================================================
   BADBAADO — Mock data layer
   Simulates the future Laravel REST API + MySQL backend.
   Endpoints in production:  POST /api/referrals, GET /api/hospitals, ...
   ================================================================ */
window.BD = (function () {

  var HOSPITALS = [
    {
      id: 'H01', name: 'BIRDEM General Hospital', short: 'BIRDEM',
      location: 'Shahbag, Dhaka', phone: '+880 2-9661551', email: 'referral@birdem.org.bd',
      er: 'available', beds: 420, occupancy: 68, level: 'Tertiary Referral',
      depts: ['Cardiology', 'Endocrinology', 'Nephrology', 'ICU & Critical Care', 'Neurology', 'Gastroenterology', 'Dialectology'],
      connections: 9
    },
    {
      id: 'H02', name: 'Dhaka Medical College Hospital', short: 'DMCH',
      location: 'Shahbag, Dhaka', phone: '+880 2-8626811', email: 'er@dmch.gov.bd',
      er: 'busy', beds: 1300, occupancy: 91, level: 'Medical College Hospital',
      depts: ['Emergency & Trauma', 'Paediatrics', 'Internal Medicine', 'Neurology', 'Orthopaedics', 'General Surgery', 'ICU & Critical Care'],
      connections: 12
    },
    {
      id: 'H03', name: 'Evercare Hospital Dhaka', short: 'Evercare',
      location: 'Bashundhara R/A, Dhaka', phone: '+880 9606-668866', email: 'er@evercare.com.bd',
      er: 'available', beds: 450, occupancy: 58, level: 'Private Tertiary',
      depts: ['Cardiology', 'Neurosurgery', 'Oncology', 'ICU & Critical Care', 'Gynaecology & Obstetrics', 'Pulmonology', 'Nephrology'],
      connections: 7
    },
    {
      id: 'H04', name: 'United Hospital Limited', short: 'United',
      location: 'Gulshan 2, Dhaka', phone: '+880 9600-200704', email: 'referrals@unitedhospital.com',
      er: 'available', beds: 520, occupancy: 74, level: 'Private Tertiary',
      depts: ['Cardiology', 'Neurology', 'General Surgery', 'ICU & Critical Care', 'Orthopaedics', 'Cardiothoracic', 'Gastroenterology'],
      connections: 8
    },
    {
      id: 'H05', name: 'Labaid Cardiac Hospital', short: 'Labaid',
      location: 'Dhanmondi, Dhaka', phone: '+880 9638-100100', email: 'er@labaidheart.com',
      er: 'busy', beds: 280, occupancy: 86, level: 'Specialised Cardiac',
      depts: ['Cardiology', 'Interventional Cardiology', 'Cardiothoracic', 'ICU & Critical Care', 'Internal Medicine'],
      connections: 5
    },
    {
      id: 'H06', name: 'Square Hospitals Ltd.', short: 'Square',
      location: 'Panchlaish, Chattogram', phone: '+880 1713-029453', email: 'referral@squarehospital.com',
      er: 'available', beds: 380, occupancy: 63, level: 'Private Tertiary',
      depts: ['Cardiology', 'Cardiothoracic', 'Gynaecology & Obstetrics', 'ICU & Critical Care', 'Oncology', 'Nephrology'],
      connections: 6
    },
    {
      id: 'H07', name: 'Chattogram Maa-O-Shishu Hospital', short: 'CMOSH',
      location: 'Agrabad, Chattogram', phone: '+880 31-716471', email: 'er@cmosh.org.bd',
      er: 'offline', beds: 300, occupancy: 0, level: 'Specialised Maternal & Child',
      depts: ['Paediatrics', 'Gynaecology & Obstetrics', 'Neonatal ICU', 'Paediatric Surgery'],
      connections: 4
    },
    {
      id: 'H08', name: 'Rajshahi Medical College Hospital', short: 'RMCH',
      location: 'Laxmipur, Rajshahi', phone: '+880 721-771015', email: 'er@rmch.gov.bd',
      er: 'offline', beds: 900, occupancy: 0, level: 'Medical College Hospital',
      depts: ['Internal Medicine', 'Neurology', 'General Surgery', 'ICU & Critical Care', 'Orthopaedics'],
      connections: 6
    }
  ];

  var DEPTS = [
    'Cardiology', 'Neurology', 'Neurosurgery', 'Emergency & Trauma',
    'Paediatrics', 'Internal Medicine', 'Orthopaedics', 'General Surgery',
    'Oncology', 'ICU & Critical Care', 'Gynaecology & Obstetrics',
    'Nephrology', 'Pulmonology', 'Gastroenterology', 'Cardiothoracic'
  ];

  var REFERRALS = [
    { id: 'REF-2026-1024', patientId: 'PT-1024', patientName: 'Tanvir Ahmed Chowdhury', age: 28, gender: 'Male', blood: 'O+',
      from: 'H03', to: 'H02', dept: 'Emergency & Trauma', reason: 'Road traffic accident, suspected spinal injury after bike collision.',
      notes: 'GCS 13 on arrival. Immobilised on backboard. CT cervical spine done — C5 wedge fracture. No neuro deficit noted yet.',
      triage: 1, time: '2026-09-12T09:41:00', status: 'in_transit',
      vital: { bp: '104/62', hr: 118, spo2: 91, resp: 24 } },
    { id: 'REF-2026-1025', patientId: 'PT-1031', patientName: 'Shahidul Islam', age: 50, gender: 'Male', blood: 'A+',
      from: 'H02', to: 'H05', dept: 'Neurology', reason: 'Sudden onset left-sided weakness and slurred speech. Suspected ischaemic stroke.',
      notes: 'Onset 45 min ago. NIHSS 9. BP 172/96. Last known well: 09:10. Stroke unit alerted, thrombectomy team on standby.',
      triage: 1, time: '2026-09-12T10:12:00', status: 'pending',
      vital: { bp: '172/96', hr: 96, spo2: 96, resp: 18 } },
    { id: 'REF-2026-1023', patientId: 'PT-1018', patientName: 'Md. Rafiqul Islam', age: 62, gender: 'Male', blood: 'B+',
      from: 'H01', to: 'H03', dept: 'Cardiology', reason: 'Acute chest pain radiating to left arm, suspected NSTEMI.',
      notes: 'First troponin elevated (1.8 ng/mL). ECG: ST depression in V3–V5. Aspirin, heparin started. Cath lab consulted.',
      triage: 2, time: '2026-09-12T08:52:00', status: 'accepted',
      vital: { bp: '142/92', hr: 104, spo2: 94, resp: 18 } },
    { id: 'REF-2026-1022', patientId: 'PT-1012', patientName: 'Rima Das', age: 6, gender: 'Female', blood: 'AB+',
      from: 'H01', to: 'H02', dept: 'Paediatrics', reason: 'High fever, vomiting and reduced urine output. Possible dengue shock.',
      notes: 'NS1 positive. Platelets 48k. Mottled skin, cap refill 4s. IV fluid bolus given, response poor.',
      triage: 2, time: '2026-09-12T08:18:00', status: 'pending',
      vital: { bp: '82/48', hr: 142, spo2: 97, resp: 32 } },
    { id: 'REF-2026-1021', patientId: 'PT-1006', patientName: 'Sumaiya Khatun', age: 45, gender: 'Female', blood: 'O-',
      from: 'H01', to: 'H05', dept: 'Neurology', reason: 'Severe sudden headache with vomiting. CTA shows possible berry aneurysm.',
      notes: 'WFNS grade 2. No SAH on CT but CTA suggests 6mm ACom aneurysm. Neurosurgical review requested.',
      triage: 3, time: '2026-09-12T07:40:00', status: 'pending',
      vital: { bp: '158/96', hr: 88, spo2: 98, resp: 16 } },
    { id: 'REF-2026-1020', patientId: 'PT-1009', patientName: 'Kazi Shah Newaj', age: 58, gender: 'Male', blood: 'A-',
      from: 'H02', to: 'H04', dept: 'Cardiothoracic', reason: 'Post-CABG day 1 cardiac arrest, ROSC achieved. Advanced cardiac support needed.',
      notes: 'ROSC in 4 min. On dobutamine. ECHO EF 32%. On IABP. ICU admission urgent.',
      triage: 1, time: '2026-09-12T06:55:00', status: 'arrived',
      vital: { bp: '98/58', hr: 112, spo2: 90, resp: 22 } },
    { id: 'REF-2026-1019', patientId: 'PT-1015', patientName: 'Humayun Kabir', age: 67, gender: 'Male', blood: 'B+',
      from: 'H04', to: 'H01', dept: 'Nephrology', reason: 'Acute kidney injury with uraemic symptoms. Emergency dialysis planning.',
      notes: 'Creatinine 6.8, K+ 6.1. Oliguric × 36h. AV fistula evaluated. HD scheduled same day.',
      triage: 3, time: '2026-09-12T05:30:00', status: 'accepted',
      vital: { bp: '148/88', hr: 78, spo2: 96, resp: 18 } },
    { id: 'REF-2026-1018', patientId: 'PT-1003', patientName: 'Farzana Akter', age: 38, gender: 'Female', blood: 'O+',
      from: 'H01', to: 'H06', dept: 'Gastroenterology', reason: 'Haematemesis with melaena. Upper GI bleeding, scope for endoscopy.',
      notes: 'Hb dropped to 7.1. 2 units PRBC transfused. Endoscopy slot reserved for today.',
      triage: 3, time: '2026-09-11T18:40:00', status: 'completed',
      vital: { bp: '118/76', hr: 92, spo2: 98, resp: 16 } },
    { id: 'REF-2026-1017', patientId: 'PT-1007', patientName: 'Ayesha Akter', age: 34, gender: 'Female', blood: 'B-',
      from: 'H06', to: 'H04', dept: 'Gynaecology & Obstetrics', reason: 'High-risk pregnancy (34 wks) with pre-eclampsia. Specialist care transfer.',
      notes: 'BP 154/98. Proteinuria 2+. Fetal growth tracking 25th centile. Nifedipine maintenance.',
      triage: 4, time: '2026-09-11T16:10:00', status: 'completed',
      vital: { bp: '154/98', hr: 84, spo2: 99, resp: 16 } },
    { id: 'REF-2026-1016', patientId: 'PT-1011', patientName: 'Abdul Karim', age: 71, gender: 'Male', blood: 'A+',
      from: 'H02', to: 'H03', dept: 'Oncology', reason: 'Cycle 3 chemotherapy continuation after CABC protocol review.',
      notes: 'ECOG 1. CBC within range. Port-a-cath functioning. Oncology day-care bed confirmed.',
      triage: 4, time: '2026-09-11T11:05:00', status: 'completed',
      vital: { bp: '132/80', hr: 70, spo2: 97, resp: 14 } },
    { id: 'REF-2026-1015', patientId: 'PT-1005', patientName: 'Nasrin Sultana', age: 29, gender: 'Female', blood: 'O+',
      from: 'H05', to: 'H01', dept: 'Endocrinology', reason: 'Diabetic ketoacidosis stabilised. Endocrine follow-up for insulin titration.',
      notes: 'pH 7.31, HCO3 14. Glucose 18.5 mmol/L. Insulin infusion tapered. Ward bed requested.',
      triage: 4, time: '2026-09-11T09:30:00', status: 'declined',
      vital: { bp: '126/78', hr: 95, spo2: 98, resp: 20 } }
  ];

  var PATIENTS = [
    { id: 'PT-1003', name: 'Farzana Akter', age: 38, gender: 'Female', blood: 'O+', phone: '+880 1712-453278',
      condition: 'Upper GI bleeding — post-endoscopy, stable', lastVisit: '2026-09-11',
      history: ['Recurrent gastritis (2023)', 'Iron deficiency anaemia (2024)', 'Open surgery necrosis — no', 'GERD on PPI (since 2022)'] },
    { id: 'PT-1005', name: 'Nasrin Sultana', age: 29, gender: 'Female', blood: 'O+', phone: '+880 1819-903455',
      condition: 'Type 1 diabetes — insulin titration ongoing', lastVisit: '2026-09-11',
      history: ['T1DM diagnosed 2018', 'DKA episode (2024)', 'Hypothyroidism (2021)'] },
    { id: 'PT-1006', name: 'Sumaiya Khatun', age: 45, gender: 'Female', blood: 'O-', phone: '+880 1612-778901',
      condition: 'Suspected cerebral aneurysm — awaiting neurosurgery consult', lastVisit: '2026-09-12',
      history: ['Chronic hypertension (10 yrs)', 'Migraine (since 2015)', 'Knee osteoarthritis (2022)'] },
    { id: 'PT-1007', name: 'Ayesha Akter', age: 34, gender: 'Female', blood: 'B-', phone: '+880 1915-334507',
      condition: 'High-risk pregnancy 34 wks — pre-eclampsia monitoring', lastVisit: '2026-09-11',
      history: ['G2P1, previous LSCS', 'Gestational diabetes (2024)', 'Pre-eclampsia risk flagged early'] },
    { id: 'PT-1012', name: 'Rima Das', age: 6, gender: 'Female', blood: 'AB+', phone: '+880 1729-112354',
      condition: 'Dengue shock syndrome — on IV fluids, monitored', lastVisit: '2026-09-12',
      history: ['Chickenpox (2021)', 'Recurrent tonsillitis (2022–2023)', 'No known allergies'] },
    { id: 'PT-1018', name: 'Md. Rafiqul Islam', age: 62, gender: 'Male', blood: 'B+', phone: '+880 1551-664290',
      condition: 'NSTEMI — post-cath, transferred to CCU', lastVisit: '2026-09-12',
      history: ['Hypertension (2010)', 'T2DM (2015)', 'Dyslipidaemia (2015)', 'Gastritis (2019)'] },
    { id: 'PT-1024', name: 'Tanvir Ahmed Chowdhury', age: 28, gender: 'Male', blood: 'O+', phone: '+880 1733-812904',
      condition: 'Trauma — C5 wedge fracture, in ambulance en route', lastVisit: '2026-09-12',
      history: ['No significant medical history', 'Road traffic accident today'] },
    { id: 'PT-1031', name: 'Shahidul Islam', age: 50, gender: 'Male', blood: 'A+', phone: '+880 1886-207415',
      condition: 'Acute ischaemic stroke — thrombectomy team alerted', lastVisit: '2026-09-12',
      history: ['Hypertension (8 yrs)', 'AF paroxysmal (2023)', 'Smoker, 20/day'] }
  ];

  var TRIAGE = {
    1: { label: 'Level 1 — Critical', cls: 'tb-1', short: 'Critical' },
    2: { label: 'Level 2 — Emergent', cls: 'tb-2', short: 'Emergent' },
    3: { label: 'Level 3 — Urgent', cls: 'tb-3', short: 'Urgent' },
    4: { label: 'Level 4 — Routine', cls: 'tb-4', short: 'Routine' }
  };

  var STATUS = {
    pending:    { label: 'Pending',    cls: 'st-pending' },
    accepted:   { label: 'Accepted',   cls: 'st-accepted' },
    in_transit: { label: 'In Transit', cls: 'st-in_transit' },
    arrived:    { label: 'Arrived',    cls: 'st-arrived' },
    completed:  { label: 'Completed',  cls: 'st-completed' },
    declined:   { label: 'Declined',   cls: 'st-declined' }
  };

  var ER = {
    available: { label: 'Available', cls: 'bg-emerald-100 text-emerald-700', dot: 'dot-available' },
    busy:      { label: 'Busy',      cls: 'bg-amber-100 text-amber-700',    dot: 'dot-busy' },
    offline:   { label: 'Offline',   cls: 'bg-slate-200 text-slate-600',    dot: 'dot-offline' }
  };

  var CURRENT_USER = {
    name: 'Dr. Farhan Kabir',
    title: 'Senior Registrar — Emergency',
    hospital: 'H01',
    email: 'farhan@birdem.org.bd'
  };

  function hospital(id) {
    for (var i = 0; i < HOSPITALS.length; i++) if (HOSPITALS[i].id === id) return HOSPITALS[i];
    return null;
  }

  function byId(refId) {
    for (var i = 0; i < REFERRALS.length; i++) if (REFERRALS[i].id === refId) return REFERRALS[i];
    return null;
  }

  function patientById(pid) {
    for (var i = 0; i < PATIENTS.length; i++) if (PATIENTS[i].id === pid) return PATIENTS[i];
    return null;
  }

  function timeAgo(iso) {
    var diff = Date.now() - new Date(iso).getTime();
    if (diff < 0) diff = 0;
    var min = Math.floor(diff / 60000);
    if (min < 1) return 'just now';
    if (min < 60) return min + ' min ago';
    var hr = Math.floor(min / 60);
    if (hr < 24) return hr + ' hr ago';
    var d = Math.floor(hr / 24);
    return d + ' day' + (d > 1 ? 's' : '') + ' ago';
  }

  function fmtShort(iso) {
    var d = new Date(iso);
    var pad = function (n) { return (n < 10 ? '0' : '') + n; };
    return pad(d.getDate()) + ' ' + ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'][d.getMonth()] + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
  }

  return {
    HOSPITALS: HOSPITALS,
    DEPTS: DEPTS,
    REFERRALS: REFERRALS,
    PATIENTS: PATIENTS,
    TRIAGE: TRIAGE,
    STATUS: STATUS,
    ER: ER,
    CURRENT_USER: CURRENT_USER,
    hospital: hospital,
    byId: byId,
    patientById: patientById,
    timeAgo: timeAgo,
    fmtShort: fmtShort
  };
})();