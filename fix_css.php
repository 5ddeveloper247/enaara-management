<?php
$file = 'public/css/employee.css';
$lines = file($file);
$out = array_slice($lines, 0, 170);
$content = implode("", $out);
$css = "
/* ============================================
   DEPARTMENT & FLOOR MULTI-SELECT CHIPS
   ============================================ */
.emp-dept-input-box {
    background: #fff;
    border: 1px solid #ced4da;
    border-radius: .75rem;
    padding: 8px 36px 8px 10px;
    min-height: 46px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
    cursor: text;
    position: relative;
}
.emp-dept-input-box.open,
.emp-dept-input-box:focus-within {
    border-color: #86b7fe;
    box-shadow: 0 0 0 3px rgba(13,110,253,.12);
}
.emp-dept-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #e9f2ff;
    border: 1px solid #b6d4fe;
    color: #0a3060;
    font-size: 12px;
    font-weight: 500;
    padding: 3px 8px 3px 10px;
    border-radius: 999px;
}
.emp-dept-ph {
    font-size: 14px;
    color: #adb5bd;
    padding: 2px 4px;
    pointer-events: none;
}
.emp-dept-chevron {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: #adb5bd;
    transition: transform .18s;
}
.emp-dept-input-box.open .emp-dept-chevron {
    transform: translateY(-50%) rotate(180deg);
}
.emp-dept-dropdown {
    background: #fff;
    border: 1px solid #ced4da;
    border-radius: .75rem;
    margin-top: 6px;
    overflow: hidden;
    z-index: 1050;
    position: relative;
}
.emp-dept-search-row {
    padding: 8px;
    border-bottom: 1px solid #f0f0f0;
}
.emp-dept-search-row input {
    width: 100%;
    border: 1px solid #ced4da;
    border-radius: 8px;
    padding: 7px 12px;
    font-size: 13px;
    background: #f8f9fa;
    color: #212529;
    outline: none;
}
.emp-dept-opt-list {
    max-height: 210px;
    overflow-y: auto;
    padding: 4px 0;
}
.emp-dept-chip-rm, .emp-floor-chip-rm {
    cursor: pointer;
    font-weight: bold;
    color: #dc3545;
    margin-left: 4px;
}
";

file_put_contents($file, $content . $css);
