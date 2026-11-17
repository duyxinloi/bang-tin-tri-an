<?php

declare(strict_types=1);

/* =====================================================================
 * TRẠM TẠO THIỆP AI - CODE THE THANKS
 * AI GREETING CARD GENERATOR  ·  Phiên bản 4.0 (Cyber AI Control Station)
 *
 * - Một file duy nhất: PHP + HTML + CSS + JavaScript thuần, chạy được trên XAMPP.
 * - Không database, không API key, không thư viện ngoài (chỉ Google Fonts, có fallback).
 * - "AI" ở đây là QUY TRÌNH MÔ PHỎNG bằng JavaScript chạy trong trình duyệt;
 *   thiệp được kết xuất thật bằng Canvas 2D và xuất ra PNG 1200 x 675.
 * ===================================================================== */

date_default_timezone_set('Asia/Ho_Chi_Minh');

/* ---------- Cấu hình ---------- */
const CARD_NAME_MAX = 30;   // số ký tự tối đa của tên người gửi
const CARD_WISH_MAX = 150;  // số ký tự tối đa của lời chúc
const CARD_WISH_MIN = 3;    // số ký tự tối thiểu của lời chúc

/* ---------- Header bảo mật ---------- */
$nonce = base64_encode(random_bytes(16));

header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header(
    "Content-Security-Policy: default-src 'self'; "
        . "script-src 'nonce-{$nonce}'; "
        . "style-src 'nonce-{$nonce}' https://fonts.googleapis.com; "
        . "font-src https://fonts.gstatic.com; "
        . "img-src 'self' data: blob:; "
        . "connect-src 'self'; object-src 'none'; base-uri 'self'; "
        . "form-action 'self'; frame-ancestors 'self'"
);

/* ---------- Tiện ích ---------- */
function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$config = [
    'nameMax' => CARD_NAME_MAX,
    'wishMax' => CARD_WISH_MAX,
    'wishMin' => CARD_WISH_MIN,
];

$now      = new DateTimeImmutable('now');
$dateText = $now->format('d/m/Y');
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#05070c">
    <meta name="description" content="Trạm tạo thiệp AI - CODE THE THANKS: tạo thiệp điện tử tri ân thầy cô nhân ngày Nhà giáo Việt Nam 20/11.">
    <title>TRẠM TẠO THIỆP AI - CODE THE THANKS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">

    <style nonce="<?= e($nonce) ?>">
        :root {
            --bg: #05070c;
            --panel: rgba(10, 15, 24, 0.9);
            --line: #1b2736;
            --line-hi: #2a3b50;
            --txt: #dbe7f3;
            --dim: #7d8ea3;
            --accent: #00ff9c;
            --accent-soft: rgba(0, 255, 156, 0.35);
            --cyan: #00e5ff;
            --pink: #ff4fd8;
            --gold: #ffd166;
            --blue: #3a86ff;
            --red: #ff5f56;
            --mono: 'JetBrains Mono', Consolas, 'Courier New', monospace;
            --sans: 'Space Grotesk', 'Segoe UI', Roboto, Arial, sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
            -webkit-text-size-adjust: 100%;
        }

        body {
            min-height: 100vh;
            background: var(--bg);
            color: var(--txt);
            font-family: var(--sans);
            line-height: 1.5;
            overflow-x: hidden;
        }

        body.no-scroll {
            overflow: hidden;
        }

        /* ===== Nền ===== */
        .rain {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            opacity: 0.22;
            pointer-events: none;
        }

        .grid-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background:
                radial-gradient(circle at 12% 18%, rgba(0, 255, 156, 0.08), transparent 32%),
                radial-gradient(circle at 88% 12%, rgba(0, 229, 255, 0.08), transparent 30%),
                radial-gradient(circle at 70% 90%, rgba(255, 79, 216, 0.07), transparent 34%),
                linear-gradient(rgba(0, 229, 255, 0.045) 1px, transparent 1px) 0 0 / 44px 44px,
                linear-gradient(90deg, rgba(0, 229, 255, 0.045) 1px, transparent 1px) 0 0 / 44px 44px;
            -webkit-mask-image: radial-gradient(ellipse at center, #000 40%, transparent 100%);
            mask-image: radial-gradient(ellipse at center, #000 40%, transparent 100%);
        }

        .app {
            position: relative;
            z-index: 1;
            max-width: 1280px;
            margin: 0 auto;
            padding: 20px 20px 40px;
        }

        .noscript {
            position: relative;
            z-index: 2;
            background: #2a0d0d;
            color: #ffb4ae;
            border-bottom: 1px solid var(--red);
            padding: 12px 20px;
            text-align: center;
            font-family: var(--mono);
        }

        /* ===== Header ===== */
        .hero {
            text-align: center;
            padding: 18px 0 8px;
        }

        .logo {
            display: inline-block;
            font-family: var(--mono);
            font-weight: 800;
            font-size: 1rem;
            letter-spacing: 1px;
            color: var(--accent);
            border: 1px solid var(--accent-soft);
            border-radius: 6px;
            padding: 5px 14px;
            background: rgba(0, 0, 0, 0.35);
            text-shadow: 0 0 10px var(--accent-soft);
            transition: color 0.4s, border-color 0.4s;
        }

        .sys-name {
            margin-top: 14px;
            font-family: var(--mono);
            font-size: 0.8rem;
            letter-spacing: 4px;
            color: var(--dim);
        }

        .title {
            margin-top: 6px;
            font-family: var(--mono);
            font-weight: 800;
            font-size: clamp(1.9rem, 6vw, 3.4rem);
            line-height: 1.1;
            letter-spacing: -1px;
            background: linear-gradient(90deg, #fff 0%, var(--accent) 45%, var(--cyan) 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0 0 18px var(--accent-soft));
        }

        .tagline {
            margin-top: 10px;
            color: #9fb0c4;
            font-family: var(--mono);
            font-size: clamp(0.8rem, 2.4vw, 1rem);
        }

        .tagline::before {
            content: "// ";
            color: var(--accent);
        }

        .hero-meta {
            margin-top: 16px;
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-family: var(--mono);
            font-size: 0.72rem;
            letter-spacing: 0.5px;
            padding: 5px 11px;
            border: 1px solid var(--line-hi);
            border-radius: 999px;
            color: #9fb0c4;
            background: rgba(0, 0, 0, 0.35);
            white-space: nowrap;
        }

        .chip-online {
            color: var(--accent);
            border-color: var(--accent-soft);
        }

        .chip-online::before,
        .chip.live::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: currentColor;
            box-shadow: 0 0 8px currentColor;
            animation: pulse 2s infinite;
        }

        .chip.is-busy {
            color: var(--gold);
            border-color: rgba(255, 209, 102, 0.5);
        }

        .chip.is-ok {
            color: var(--accent);
            border-color: var(--accent-soft);
        }

        .chip.is-err {
            color: var(--red);
            border-color: rgba(255, 95, 86, 0.6);
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.35;
            }
        }

        /* ===== Bảng trạng thái ===== */
        .status {
            margin: 22px 0 10px;
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
        }

        .st {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 10px 14px;
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }

        .st-k {
            font-family: var(--mono);
            font-size: 0.66rem;
            letter-spacing: 1.5px;
            color: var(--dim);
        }

        .st-v {
            font-family: var(--mono);
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--cyan);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .st-v.ok {
            color: var(--accent);
        }

        .st-v.busy {
            color: var(--gold);
        }

        .st-v.bad {
            color: var(--red);
        }

        .disclaimer {
            text-align: center;
            font-size: 0.78rem;
            color: var(--dim);
            font-family: var(--mono);
            margin-bottom: 18px;
        }

        /* ===== Hướng dẫn ===== */
        .steps {
            list-style: none;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 22px;
        }

        .steps li {
            display: flex;
            gap: 10px;
            align-items: center;
            font-size: 0.85rem;
            color: #a9b8ca;
            padding: 8px 12px;
            border-left: 2px solid var(--accent);
            background: rgba(255, 255, 255, 0.02);
            border-radius: 0 8px 8px 0;
        }

        .steps b {
            font-family: var(--mono);
            color: var(--accent);
            font-size: 0.95rem;
        }

        /* ===== Lưới chính ===== */
        .station {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1.15fr);
            gap: 24px;
            align-items: start;
        }

        .panel {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 14px;
            box-shadow: 0 24px 50px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            position: relative;
            transition: box-shadow 0.4s, border-color 0.4s;
        }

        .panel:hover {
            border-color: var(--line-hi);
        }

        .panel.glow-done {
            animation: doneGlow 1.6s ease-out;
        }

        @keyframes doneGlow {
            0% {
                box-shadow: 0 0 0 0 var(--accent-soft), 0 24px 50px rgba(0, 0, 0, 0.5);
                border-color: var(--accent);
            }

            40% {
                box-shadow: 0 0 40px 4px var(--accent-soft), 0 24px 50px rgba(0, 0, 0, 0.5);
                border-color: var(--accent);
            }

            100% {
                box-shadow: 0 0 0 0 transparent, 0 24px 50px rgba(0, 0, 0, 0.5);
            }
        }

        /* ===== Terminal ===== */
        .term-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 11px 16px;
            background: #0f1620;
            border-bottom: 1px solid var(--line);
        }

        .term-bar i {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .term-bar i:nth-child(1) {
            background: #ff5f56;
        }

        .term-bar i:nth-child(2) {
            background: #ffbd2e;
        }

        .term-bar i:nth-child(3) {
            background: #27c93f;
        }

        .term-bar span {
            margin-left: 8px;
            font-family: var(--mono);
            font-size: 0.78rem;
            color: var(--dim);
        }

        .term-log {
            height: 250px;
            overflow-y: auto;
            padding: 14px 16px;
            background: rgba(0, 0, 0, 0.35);
            font-family: var(--mono);
            font-size: 0.8rem;
            line-height: 1.65;
            scrollbar-width: thin;
            scrollbar-color: #2a3b50 transparent;
            position: relative;
        }

        .term-log::after {
            content: "";
            position: sticky;
            display: block;
            bottom: 0;
            height: 0;
        }

        .tl {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .tl.sys {
            color: #8b9bb0;
        }

        .tl.dim {
            color: #5f7188;
        }

        .tl.cmd {
            color: #fff;
        }

        .tl.ok {
            color: var(--accent);
        }

        .tl.step {
            color: var(--cyan);
        }

        .tl.sub {
            color: #6f8299;
        }

        .tl.warn {
            color: var(--gold);
        }

        .tl.err {
            color: var(--red);
        }

        .tl.done {
            color: var(--accent);
            font-weight: 700;
            text-shadow: 0 0 10px var(--accent-soft);
        }

        .cursor {
            display: inline-block;
            width: 8px;
            height: 1.05em;
            margin-left: 3px;
            vertical-align: text-bottom;
            background: var(--accent);
            box-shadow: 0 0 8px var(--accent);
            animation: blink 1s step-end infinite;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0;
            }
        }

        .prog {
            padding: 9px 16px 11px;
            background: #0b1119;
            border-top: 1px solid var(--line);
            border-bottom: 1px solid var(--line);
        }

        .prog-top {
            display: flex;
            justify-content: space-between;
            font-family: var(--mono);
            font-size: 0.72rem;
            color: var(--dim);
            margin-bottom: 6px;
        }

        .prog-top b {
            color: var(--accent);
        }

        .prog-track {
            height: 6px;
            background: #1b2736;
            border-radius: 99px;
            overflow: hidden;
        }

        .prog-bar {
            height: 100%;
            width: 0;
            border-radius: 99px;
            background: linear-gradient(90deg, var(--accent), var(--cyan));
            box-shadow: 0 0 12px var(--accent);
            transition: width 0.45s ease;
        }

        .prog-bar.err {
            background: var(--red);
            box-shadow: 0 0 12px var(--red);
        }

        /* ===== Form ===== */
        .term-form {
            padding: 18px 18px 16px;
        }

        .field {
            margin-bottom: 16px;
            border: 0;
            min-width: 0;
        }

        .field>label,
        .field>legend {
            display: block;
            font-family: var(--mono);
            font-size: 0.78rem;
            color: var(--cyan);
            margin-bottom: 6px;
            padding: 0;
        }

        .field>label::before,
        .field>legend::before {
            content: "> ";
            color: var(--accent);
        }

        .term-form input[type="text"],
        .term-form textarea {
            width: 100%;
            background: #03060a;
            border: 1px solid var(--line-hi);
            color: #d3deea;
            padding: 10px 12px;
            border-radius: 8px;
            font-family: var(--mono);
            font-size: 0.9rem;
            transition: border-color 0.25s, box-shadow 0.25s, opacity 0.25s;
        }

        .term-form textarea {
            height: 88px;
            resize: none;
            line-height: 1.5;
        }

        .term-form input::placeholder,
        .term-form textarea::placeholder {
            color: #44566b;
        }

        .term-form input[type="text"]:focus,
        .term-form textarea:focus {
            outline: none;
            border-color: var(--cyan);
            box-shadow: 0 0 0 1px var(--cyan), 0 0 16px rgba(0, 229, 255, 0.18);
        }

        .term-form input[type="text"]:disabled {
            opacity: 0.4;
        }

        .field.invalid input[type="text"],
        .field.invalid textarea {
            border-color: var(--red);
            box-shadow: 0 0 0 1px var(--red);
        }

        .field.shake {
            animation: shake 0.4s;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-6px);
            }

            75% {
                transform: translateX(6px);
            }
        }

        .err {
            min-height: 1.1em;
            margin-top: 4px;
            font-family: var(--mono);
            font-size: 0.74rem;
            color: var(--red);
        }

        .check {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
            font-size: 0.82rem;
            color: #a9b8ca;
            cursor: pointer;
            user-select: none;
        }

        .check input {
            width: 16px;
            height: 16px;
            accent-color: var(--accent);
            cursor: pointer;
        }

        .tools {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-top: 6px;
            flex-wrap: wrap;
        }

        .link-btn {
            background: none;
            border: 1px dashed rgba(255, 209, 102, 0.5);
            color: var(--gold);
            font-family: var(--mono);
            font-size: 0.75rem;
            padding: 5px 10px;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.2s;
        }

        .link-btn:hover {
            background: rgba(255, 209, 102, 0.1);
            box-shadow: 0 0 12px rgba(255, 209, 102, 0.25);
        }

        .link-btn:active {
            transform: scale(0.97);
        }

        .count {
            font-family: var(--mono);
            font-size: 0.74rem;
            color: var(--dim);
        }

        .count.warn {
            color: var(--gold);
        }

        .count.full {
            color: var(--red);
        }

        /* Chọn theme */
        .themes {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
        }

        .theme {
            position: relative;
            display: block;
            cursor: pointer;
            --c: #00ff9c;
            --cs: rgba(0, 255, 156, 0.4);
        }

        .theme[data-t="cyan"] {
            --c: #00e5ff;
            --cs: rgba(0, 229, 255, 0.4);
        }

        .theme[data-t="synth"] {
            --c: #ff4fd8;
            --cs: rgba(255, 79, 216, 0.4);
        }

        .theme[data-t="gold"] {
            --c: #ffd166;
            --cs: rgba(255, 209, 102, 0.4);
        }

        .theme[data-t="blue"] {
            --c: #3a86ff;
            --cs: rgba(58, 134, 255, 0.4);
        }

        .theme input {
            position: absolute;
            opacity: 0;
            inset: 0;
            cursor: pointer;
        }

        .theme-card {
            display: flex;
            flex-direction: column;
            gap: 5px;
            padding: 7px;
            border: 1px solid var(--line-hi);
            border-radius: 9px;
            background: #03060a;
            transition: 0.25s;
            height: 100%;
        }

        .theme-card b {
            font-family: var(--mono);
            font-size: 0.68rem;
            color: #b9c7d8;
            letter-spacing: 0.3px;
        }

        .theme-card small {
            font-size: 0.62rem;
            color: #5f7188;
            line-height: 1.25;
        }

        .sw {
            display: block;
            height: 34px;
            border-radius: 5px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .theme[data-t="matrix"] .sw {
            background:
                repeating-linear-gradient(90deg, transparent 0 7px, rgba(0, 255, 156, 0.25) 7px 8px),
                linear-gradient(135deg, #020a06, #06301d 55%, #00ff9c);
        }

        .theme[data-t="cyan"] .sw {
            background:
                repeating-linear-gradient(0deg, transparent 0 7px, rgba(0, 229, 255, 0.22) 7px 8px),
                linear-gradient(135deg, #050b14, #0a3a5c 55%, #00e5ff);
        }

        .theme[data-t="synth"] .sw {
            background: linear-gradient(135deg, #1a0536, #8a1fa8 45%, #ff4fd8 75%, #ffd166);
        }

        .theme[data-t="gold"] .sw {
            background: linear-gradient(135deg, #0d0d0d, #5c4410 50%, #ffd166);
        }

        .theme[data-t="blue"] .sw {
            background:
                radial-gradient(circle at 70% 40%, rgba(255, 255, 255, 0.5) 0 1.5px, transparent 2px),
                linear-gradient(135deg, #020617, #0f2a66 55%, #3a86ff);
        }

        .theme:hover .theme-card {
            border-color: var(--c);
            transform: translateY(-2px);
        }

        .theme input:checked+.theme-card {
            border-color: var(--c);
            box-shadow: 0 0 0 1px var(--c), 0 0 18px var(--cs);
        }

        .theme input:checked+.theme-card b {
            color: var(--c);
        }

        .theme input:focus-visible+.theme-card {
            outline: 2px solid #fff;
            outline-offset: 2px;
        }

        /* Nút */
        .actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            font-family: var(--mono);
            font-weight: 700;
            font-size: 0.82rem;
            letter-spacing: 0.3px;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid transparent;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-align: center;
            transition: transform 0.15s, box-shadow 0.25s, background 0.25s, color 0.25s, border-color 0.25s;
        }

        .btn:active:not(:disabled) {
            transform: scale(0.97);
        }

        .btn:focus-visible,
        .link-btn:focus-visible,
        .lb-close:focus-visible {
            outline: 2px solid #fff;
            outline-offset: 2px;
        }

        .btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .btn-primary {
            flex: 1;
            background: var(--accent);
            color: #021208;
            box-shadow: 0 0 18px var(--accent-soft);
        }

        .btn-primary:hover:not(:disabled) {
            background: #fff;
            box-shadow: 0 0 26px var(--accent);
        }

        .btn-ghost {
            background: transparent;
            border-color: var(--line-hi);
            color: #9fb0c4;
        }

        .btn-ghost:hover:not(:disabled) {
            border-color: var(--red);
            color: var(--red);
        }

        .btn-cyan {
            flex: 1.4;
            background: var(--cyan);
            color: #001418;
            box-shadow: 0 0 16px rgba(0, 229, 255, 0.25);
        }

        .btn-cyan:hover:not(:disabled) {
            background: #fff;
            box-shadow: 0 0 24px rgba(0, 229, 255, 0.6);
        }

        .btn-gold {
            flex: 1;
            background: transparent;
            border-color: rgba(255, 209, 102, 0.6);
            color: var(--gold);
        }

        .btn-gold:hover:not(:disabled) {
            background: var(--gold);
            color: #1a1200;
        }

        .btn-view {
            flex: 1;
            background: transparent;
            border-color: rgba(0, 229, 255, 0.5);
            color: var(--cyan);
        }

        .btn-view:hover:not(:disabled) {
            background: rgba(0, 229, 255, 0.12);
        }

        .hint {
            margin-top: 12px;
            font-family: var(--mono);
            font-size: 0.72rem;
            color: #5f7188;
        }

        .hint kbd {
            font-family: var(--mono);
            border: 1px solid var(--line-hi);
            border-bottom-width: 2px;
            border-radius: 4px;
            padding: 0 5px;
            color: #9fb0c4;
        }

        /* ===== Preview ===== */
        .preview {
            padding: 18px;
        }

        .pv-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .pv-head h2 {
            font-family: var(--mono);
            font-size: 1rem;
            color: #fff;
            letter-spacing: 1px;
        }

        .pv-head h2::before {
            content: "● ";
            color: var(--accent);
        }

        .pv-meta {
            display: grid;
            grid-template-columns: repeat(4, auto);
            justify-content: space-between;
            gap: 8px 14px;
            margin-bottom: 14px;
            padding: 9px 12px;
            border: 1px dashed var(--line-hi);
            border-radius: 8px;
            font-family: var(--mono);
            font-size: 0.7rem;
        }

        .pv-meta div {
            min-width: 0;
        }

        .pv-meta dt {
            color: #5f7188;
            letter-spacing: 1px;
            font-size: 0.62rem;
        }

        .pv-meta dd {
            color: #b9c7d8;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .stage {
            position: relative;
            width: 100%;
            aspect-ratio: 16 / 9;
            background:
                linear-gradient(rgba(0, 229, 255, 0.04) 1px, transparent 1px) 0 0 / 24px 24px,
                linear-gradient(90deg, rgba(0, 229, 255, 0.04) 1px, transparent 1px) 0 0 / 24px 24px,
                #02040a;
            border: 1px solid var(--line);
            border-radius: 10px;
            overflow: hidden;
        }

        .stage.done {
            animation: stageGlow 1.5s ease-out;
        }

        @keyframes stageGlow {
            0% {
                box-shadow: 0 0 0 0 var(--accent-soft);
            }

            35% {
                box-shadow: 0 0 36px 2px var(--accent-soft);
            }

            100% {
                box-shadow: 0 0 0 0 transparent;
            }
        }

        .placeholder {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-family: var(--mono);
            font-size: 0.8rem;
            color: #34475c;
            text-align: center;
            padding: 10px;
            transition: opacity 0.3s;
        }

        .placeholder svg {
            width: 46px;
            height: 46px;
            opacity: 0.6;
        }

        .stage.has-card .placeholder {
            display: none;
        }

        .stage.processing .placeholder {
            opacity: 0.2;
        }

        #cardCanvas {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            display: none;
        }

        .stage.has-card #cardCanvas {
            display: block;
            animation: bootUp 0.9s cubic-bezier(0.1, 0.8, 0.2, 1);
        }

        @keyframes bootUp {
            0% {
                filter: blur(12px) brightness(3) saturate(0);
                transform: scale(1.04);
                opacity: 0.4;
            }

            100% {
                filter: none;
                transform: none;
                opacity: 1;
            }
        }

        .scan {
            position: absolute;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            background: linear-gradient(to bottom, transparent 0%, var(--accent-soft) 50%, transparent 100%) 0 -100% / 100% 40% no-repeat;
            animation: scanMove 1.4s linear infinite;
        }

        .scan span {
            font-family: var(--mono);
            font-weight: 700;
            letter-spacing: 3px;
            color: var(--accent);
            text-shadow: 0 0 12px var(--accent);
            font-size: 0.9rem;
        }

        .stage.processing .scan {
            display: flex;
        }

        @keyframes scanMove {
            to {
                background-position: 0 200%;
            }
        }

        .pv-actions {
            display: flex;
            gap: 10px;
            margin-top: 16px;
            flex-wrap: wrap;
        }

        /* ===== Footer ===== */
        .foot {
            margin-top: 34px;
            text-align: center;
            font-family: var(--mono);
            font-size: 0.75rem;
            color: #5f7188;
            line-height: 1.8;
        }

        .foot b {
            color: var(--accent);
        }

        /* ===== Toast ===== */
        .toast {
            position: fixed;
            left: 50%;
            bottom: 24px;
            transform: translate(-50%, 30px);
            opacity: 0;
            pointer-events: none;
            z-index: 60;
            max-width: calc(100vw - 32px);
            padding: 11px 18px;
            border-radius: 10px;
            background: #07120d;
            border: 1px solid var(--accent);
            color: var(--accent);
            font-family: var(--mono);
            font-size: 0.8rem;
            box-shadow: 0 0 24px var(--accent-soft);
            transition: opacity 0.3s, transform 0.3s;
        }

        .toast.show {
            opacity: 1;
            transform: translate(-50%, 0);
        }

        .toast.error {
            background: #1a0b0a;
            border-color: var(--red);
            color: var(--red);
            box-shadow: 0 0 24px rgba(255, 95, 86, 0.3);
        }

        /* ===== Xem toàn màn hình ===== */
        .lightbox {
            position: fixed;
            inset: 0;
            z-index: 80;
            background: rgba(2, 4, 8, 0.94);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 16px;
            cursor: zoom-out;
        }

        .lightbox[hidden] {
            display: none;
        }

        .lightbox img {
            max-width: 100%;
            max-height: calc(100vh - 110px);
            aspect-ratio: 16 / 9;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 0 60px var(--accent-soft);
            cursor: default;
        }

        .lightbox p {
            font-family: var(--mono);
            font-size: 0.74rem;
            color: #7d8ea3;
            text-align: center;
        }

        .lb-close {
            position: absolute;
            top: 14px;
            right: 14px;
            background: transparent;
            border: 1px solid var(--line-hi);
            color: #c9d6e5;
            font-family: var(--mono);
            padding: 8px 14px;
            border-radius: 8px;
            cursor: pointer;
        }

        .lb-close:hover {
            border-color: var(--red);
            color: var(--red);
        }

        /* ===== Responsive ===== */
        @media (max-width: 1000px) {
            .station {
                grid-template-columns: minmax(0, 1fr);
            }

            .status {
                grid-template-columns: repeat(3, 1fr);
            }

            .steps {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .app {
                padding: 14px 12px 30px;
            }

            .status {
                grid-template-columns: repeat(2, 1fr);
            }

            .themes {
                grid-template-columns: repeat(3, 1fr);
            }

            .term-log {
                height: 210px;
                font-size: 0.74rem;
            }

            .pv-meta {
                grid-template-columns: repeat(2, 1fr);
            }

            .actions,
            .pv-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            .preview {
                padding: 12px;
            }

            .steps {
                grid-template-columns: 1fr;
            }
        }

        /* ===== Giảm chuyển động ===== */
        @media (prefers-reduced-motion: reduce) {
            html {
                scroll-behavior: auto;
            }

            *,
            *::before,
            *::after {
                animation-duration: 0.001ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.001ms !important;
            }

            .rain {
                display: none;
            }

            .cursor {
                animation: none;
            }
        }
    </style>
</head>

<body>
    <canvas id="rain" class="rain" aria-hidden="true"></canvas>
    <div class="grid-bg" aria-hidden="true"></div>

    <noscript>
        <div class="noscript">Trạm tạo thiệp cần JavaScript để hoạt động. Vui lòng bật JavaScript trong trình duyệt.</div>
    </noscript>

    <div class="app">
        <!-- ================= HEADER ================= -->
        <header class="hero">
            <div class="logo">&lt;/&gt; CODE THE THANKS</div>
            <div class="sys-name">AI GREETING CARD GENERATOR</div>
            <h1 class="title">TRẠM TẠO THIỆP AI</h1>
            <p class="tagline">Compile your gratitude into a beautiful memory.</p>
            <div class="hero-meta">
                <span class="chip chip-online">SYSTEM ONLINE</span>
                <span class="chip" id="clock"><?= e($dateText) ?></span>
            </div>
        </header>

        <!-- ================= TRẠNG THÁI HỆ THỐNG ================= -->
        <section class="status" aria-label="Trạng thái hệ thống">
            <div class="st"><span class="st-k">AI ENGINE</span><span class="st-v ok" id="stEngine">ONLINE</span></div>
            <div class="st"><span class="st-k">RENDER ENGINE</span><span class="st-v ok" id="stRender">READY</span></div>
            <div class="st"><span class="st-k">CANVAS</span><span class="st-v">1200 × 675</span></div>
            <div class="st"><span class="st-k">EXPORT FORMAT</span><span class="st-v">PNG</span></div>
            <div class="st"><span class="st-k">CONNECTION</span><span class="st-v">LOCAL SYSTEM</span></div>
        </section>
        <p class="disclaimer">* Quy trình “AI” là mô phỏng bằng JavaScript chạy hoàn toàn trên trình duyệt của bạn. Không có API hay mô hình AI bên ngoài nào được gọi.</p>

        <!-- ================= HƯỚNG DẪN NGẮN ================= -->
        <ol class="steps" aria-label="Quy trình sử dụng">
            <li><b>01</b> Nhập tên &amp; lời chúc</li>
            <li><b>02</b> Chọn phong cách thiệp</li>
            <li><b>03</b> Nhấn INITIALIZE AI GENERATOR</li>
            <li><b>04</b> Xem trước &amp; tải thiệp PNG</li>
        </ol>

        <!-- ================= TRẠM ĐIỀU KHIỂN ================= -->
        <main class="station">
            <!-- ---------- TERMINAL ---------- -->
            <section class="panel" id="terminalPanel" aria-label="Terminal nhập liệu">
                <div class="term-bar">
                    <i></i><i></i><i></i>
                    <span>root@thanks-station:~/ai-generator</span>
                </div>

                <div class="term-log" id="termLog" role="log" aria-label="Nhật ký hệ thống" tabindex="0"></div>

                <div class="prog">
                    <div class="prog-top">
                        <span id="progLabel">STATUS: IDLE</span>
                        <b id="progPct">0%</b>
                    </div>
                    <div class="prog-track" id="progTrack" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" aria-label="Tiến trình kết xuất">
                        <div class="prog-bar" id="progBar"></div>
                    </div>
                </div>

                <form class="term-form" id="genForm" novalidate autocomplete="off">
                    <div class="field" id="fieldName">
                        <label for="senderName">var sender_name =</label>
                        <input type="text" id="senderName" maxlength="<?= CARD_NAME_MAX ?>" placeholder="Nguyễn Văn A" spellcheck="false" autocomplete="off">
                        <label class="check"><input type="checkbox" id="anonymous"> Gửi ẩn danh</label>
                        <p class="err" id="errName" role="alert"></p>
                    </div>

                    <div class="field" id="fieldWish">
                        <label for="wishText">const gratitude_message =</label>
                        <textarea id="wishText" maxlength="<?= CARD_WISH_MAX ?>" rows="3" placeholder="Nhập lời chúc nhân ngày 20/11..." spellcheck="false"></textarea>
                        <div class="tools">
                            <button type="button" class="link-btn" id="btnRandom" title="RANDOM WISH">⚡ GỢI Ý LỜI CHÚC</button>
                            <span class="count" id="wishCount">0/<?= CARD_WISH_MAX ?> · còn <?= CARD_WISH_MAX ?></span>
                        </div>
                        <p class="err" id="errWish" role="alert"></p>
                    </div>

                    <fieldset class="field">
                        <legend>config.theme =</legend>
                        <div class="themes">
                            <label class="theme" data-t="matrix"><input type="radio" name="theme" value="matrix" checked><span class="theme-card"><i class="sw"></i><b>MATRIX</b><small>Neon xanh, mã nhị phân</small></span></label>
                            <label class="theme" data-t="cyan"><input type="radio" name="theme" value="cyan"><span class="theme-card"><i class="sw"></i><b>NEON CYAN</b><small>Mạch điện tử, ánh sáng cyan</small></span></label>
                            <label class="theme" data-t="synth"><input type="radio" name="theme" value="synth"><span class="theme-card"><i class="sw"></i><b>SYNTHWAVE</b><small>Tím, hồng, vàng retro</small></span></label>
                            <label class="theme" data-t="gold"><input type="radio" name="theme" value="gold"><span class="theme-card"><i class="sw"></i><b>GOLDEN CODE</b><small>Vàng kim sang trọng</small></span></label>
                            <label class="theme" data-t="blue"><input type="radio" name="theme" value="blue"><span class="theme-card"><i class="sw"></i><b>BLUE FUTURE</b><small>Xanh dương tối giản</small></span></label>
                        </div>
                    </fieldset>

                    <div class="actions">
                        <button type="submit" class="btn btn-primary" id="btnGenerate">▶ INITIALIZE AI GENERATOR</button>
                        <button type="button" class="btn btn-ghost" id="btnReset">↺ RESET SYSTEM</button>
                    </div>
                    <p class="hint">Mẹo: nhấn <kbd>Enter</kbd> hoặc <kbd>Ctrl</kbd> + <kbd>Enter</kbd> để tạo thiệp nhanh.</p>
                </form>
            </section>

            <!-- ---------- PREVIEW ---------- -->
            <section class="panel preview" aria-label="Xem trước thiệp">
                <div class="pv-head">
                    <h2>LIVE CARD PREVIEW</h2>
                    <span class="chip live" id="pvStatus" role="status">WAITING FOR COMPILATION</span>
                </div>

                <dl class="pv-meta">
                    <div>
                        <dt>FILE</dt>
                        <dd id="metaFile">output_buffer.png</dd>
                    </div>
                    <div>
                        <dt>SIZE</dt>
                        <dd>1200 × 675 px</dd>
                    </div>
                    <div>
                        <dt>FORMAT</dt>
                        <dd>PNG</dd>
                    </div>
                    <div>
                        <dt>COMPILE TIME</dt>
                        <dd id="metaTime">—</dd>
                    </div>
                </dl>

                <div class="stage" id="stage">
                    <div class="placeholder">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                        <p>WAITING FOR COMPILATION<span class="cursor"></span></p>
                    </div>
                    <canvas id="cardCanvas" width="1200" height="675" aria-label="Thiệp điện tử tri ân thầy cô"></canvas>
                    <div class="scan" aria-hidden="true"><span>COMPILING…</span></div>
                </div>

                <div class="pv-actions">
                    <button type="button" class="btn btn-cyan" id="btnDownload" disabled>⬇ DOWNLOAD GREETING CARD</button>
                    <button type="button" class="btn btn-gold" id="btnRegen" disabled>⟳ REGENERATE</button>
                    <button type="button" class="btn btn-view" id="btnFull" disabled>⛶ XEM LỚN</button>
                </div>
            </section>
        </main>

        <footer class="foot">
            <b>&lt;/&gt; CODE THE THANKS</b> · Tri ân thầy cô nhân ngày Nhà giáo Việt Nam 20/11<br>
            Kết xuất bằng Canvas 2D ngay trên máy của bạn · Dữ liệu nhập không được gửi đi hay lưu trữ ở đâu cả.
        </footer>
    </div>

    <div class="toast" id="toast" role="status" aria-live="polite"></div>

    <div class="lightbox" id="lightbox" hidden role="dialog" aria-modal="true" aria-label="Xem thiệp kích thước lớn">
        <button type="button" class="lb-close" id="lbClose">✕ ĐÓNG (Esc)</button>
        <img id="lbImg" alt="Thiệp tri ân">
        <p id="lbHint">Nhấn ra ngoài hoặc phím Esc để đóng.</p>
    </div>

    <script nonce="<?= e($nonce) ?>">
        (() => {
            'use strict';

            const CFG = <?= json_encode($config, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

            /* ------------------------------------------------------------------
             * Hằng số & cấu hình theme
             * ------------------------------------------------------------------ */
            const W = 1200;
            const H = 675;
            const MONO = '"JetBrains Mono", Consolas, "Courier New", monospace';
            const SANS = '"Space Grotesk", "Segoe UI", Arial, sans-serif';

            const WISHES = [
                'Cảm ơn thầy cô đã tận tâm dìu dắt chúng em trên hành trình tri thức.',
                'Chúc thầy cô luôn mạnh khỏe, hạnh phúc và giữ mãi ngọn lửa nhiệt huyết.',
                'Những bài học hôm nay sẽ là hành trang quý giá cho tương lai chúng em.',
                'Cảm ơn thầy cô đã kiên nhẫn debug từng lỗi sai để chúng em trưởng thành hơn mỗi ngày. Chúc mừng 20/11!',
                'Một chữ cũng là thầy, nửa chữ cũng là thầy. Chúc thầy cô ngày 20/11 thật nhiều niềm vui và sức khỏe.',
                'Cảm ơn thầy cô đã trao cho chúng em tri thức, niềm tin và cả ước mơ. Kính chúc thầy cô ngày 20/11 ấm áp!'
            ];

            const THEMES = {
                matrix: {
                    key: 'matrix',
                    label: 'MATRIX',
                    bg1: '#010806',
                    bg2: '#04180f',
                    accent: '#00ff9c',
                    glow2: '#00e5ff',
                    main: '#e6fff4',
                    kw: '#00e5ff',
                    str: '#b6ff8a',
                    prop: '#7dffc8',
                    punc: '#7fa593',
                    cmt: '#4f7a65'
                },
                cyan: {
                    key: 'cyan',
                    label: 'NEON CYAN',
                    bg1: '#040a14',
                    bg2: '#08213a',
                    accent: '#00e5ff',
                    glow2: '#3a86ff',
                    main: '#eaf8ff',
                    kw: '#ff79c6',
                    str: '#f1fa8c',
                    prop: '#8be9fd',
                    punc: '#8fb0cc',
                    cmt: '#4d6d8a'
                },
                synth: {
                    key: 'synth',
                    label: 'SYNTHWAVE',
                    bg1: '#0f0320',
                    bg2: '#2d0b4a',
                    accent: '#ff4fd8',
                    glow2: '#ffd166',
                    main: '#fff0fb',
                    kw: '#72f1ff',
                    str: '#ffd166',
                    prop: '#ff9be8',
                    punc: '#c6a8e0',
                    cmt: '#7d5aa0'
                },
                gold: {
                    key: 'gold',
                    label: 'GOLDEN CODE',
                    bg1: '#0a0805',
                    bg2: '#201807',
                    accent: '#ffd166',
                    glow2: '#ff9f1c',
                    main: '#fdf6e3',
                    kw: '#e5a8ff',
                    str: '#ffe29a',
                    prop: '#ffd166',
                    punc: '#b8a98a',
                    cmt: '#7a6a45'
                },
                blue: {
                    key: 'blue',
                    label: 'BLUE FUTURE',
                    bg1: '#020512',
                    bg2: '#0b1a3d',
                    accent: '#3a86ff',
                    glow2: '#38bdf8',
                    main: '#f1f6ff',
                    kw: '#38bdf8',
                    str: '#a3e635',
                    prop: '#7fb0ff',
                    punc: '#8da2c0',
                    cmt: '#4b5f80'
                }
            };

            /* ------------------------------------------------------------------
             * DOM
             * ------------------------------------------------------------------ */
            const $ = (id) => document.getElementById(id);
            const el = {
                log: $('termLog'),
                bar: $('progBar'),
                pct: $('progPct'),
                progLabel: $('progLabel'),
                progTrack: $('progTrack'),
                form: $('genForm'),
                name: $('senderName'),
                anon: $('anonymous'),
                wish: $('wishText'),
                count: $('wishCount'),
                fieldName: $('fieldName'),
                fieldWish: $('fieldWish'),
                errName: $('errName'),
                errWish: $('errWish'),
                btnRandom: $('btnRandom'),
                btnGen: $('btnGenerate'),
                btnReset: $('btnReset'),
                btnDl: $('btnDownload'),
                btnRegen: $('btnRegen'),
                btnFull: $('btnFull'),
                stage: $('stage'),
                canvas: $('cardCanvas'),
                pvStatus: $('pvStatus'),
                metaFile: $('metaFile'),
                metaTime: $('metaTime'),
                stEngine: $('stEngine'),
                stRender: $('stRender'),
                terminal: $('terminalPanel'),
                toast: $('toast'),
                lb: $('lightbox'),
                lbImg: $('lbImg'),
                lbHint: $('lbHint'),
                lbClose: $('lbClose'),
                clock: $('clock'),
                rain: $('rain')
            };

            const ctx = el.canvas.getContext ? el.canvas.getContext('2d') : null;
            const reduceMotion = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : {
                matches: false
            };

            let busy = false;
            let hasCard = false;
            let lastFile = '';
            let lastWishIdx = -1;
            let toastTimer = 0;
            let lbUrl = '';
            let currentTheme = THEMES.matrix;

            /* ------------------------------------------------------------------
             * Tiện ích
             * ------------------------------------------------------------------ */
            const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

            function rgba(hex, a) {
                const h = hex.replace('#', '');
                const n = parseInt(h.length === 3 ? h.split('').map((c) => c + c).join('') : h, 16);
                return 'rgba(' + ((n >> 16) & 255) + ',' + ((n >> 8) & 255) + ',' + (n & 255) + ',' + a + ')';
            }

            function rng(seed) {
                let a = seed >>> 0;
                return () => {
                    a = (a + 0x6D2B79F5) | 0;
                    let t = Math.imul(a ^ (a >>> 15), 1 | a);
                    t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
                    return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
                };
            }

            function hashStr(s) {
                let h = 2166136261;
                for (let i = 0; i < s.length; i++) {
                    h ^= s.charCodeAt(i);
                    h = Math.imul(h, 16777619);
                }
                return h >>> 0;
            }

            // Làm sạch dữ liệu nhập: chuẩn hóa Unicode (dấu tiếng Việt), bỏ ký tự điều khiển, gộp khoảng trắng.
            function sanitize(value, max) {
                let s = String(value == null ? '' : value).normalize('NFC');
                s = s.replace(/[\u0000-\u001F\u007F-\u009F\u200B-\u200F\u202A-\u202E\u2066-\u2069\uFEFF]/g, ' ');
                s = s.replace(/\s+/g, ' ').trim();
                return Array.from(s).slice(0, max).join('');
            }

            function slugify(s) {
                const r = s.toLowerCase()
                    .replace(/đ/g, 'd')
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '')
                    .slice(0, 40)
                    .replace(/-+$/g, '');
                return r || 'an-danh';
            }

            function showToast(msg, type) {
                el.toast.textContent = msg;
                el.toast.classList.toggle('error', type === 'error');
                el.toast.classList.add('show');
                clearTimeout(toastTimer);
                toastTimer = setTimeout(() => el.toast.classList.remove('show'), 3200);
            }

            function setChip(text, state) {
                el.pvStatus.textContent = text;
                el.pvStatus.className = 'chip' + (state ? ' is-' + state : ' live');
            }

            function applyTheme(th) {
                currentTheme = th;
                const root = document.documentElement.style;
                root.setProperty('--accent', th.accent);
                root.setProperty('--accent-soft', rgba(th.accent, 0.35));
            }

            function selectedThemeKey() {
                const checked = document.querySelector('input[name="theme"]:checked');
                return checked && THEMES[checked.value] ? checked.value : 'matrix';
            }

            /* ------------------------------------------------------------------
             * Terminal: hiệu ứng đánh máy (hàng đợi tuần tự)
             * ------------------------------------------------------------------ */
            const cursor = document.createElement('span');
            cursor.className = 'cursor';
            let chain = Promise.resolve();
            let epoch = 0;

            function scrollLog() {
                el.log.scrollTop = el.log.scrollHeight;
            }

            function newLine(cls) {
                const d = document.createElement('div');
                d.className = 'tl ' + (cls || '');
                el.log.appendChild(d);
                el.log.appendChild(cursor);
                while (el.log.childElementCount > 260) {
                    el.log.removeChild(el.log.firstElementChild);
                }
                return d;
            }

            function say(text, cls, speed) {
                const my = epoch;
                const sp = typeof speed === 'number' ? speed : 12;
                chain = chain.then(async () => {
                    if (my !== epoch) return;
                    const d = newLine(cls);
                    if (reduceMotion.matches || sp === 0) {
                        d.textContent = text;
                        scrollLog();
                        return;
                    }
                    const chars = Array.from(text);
                    for (let i = 0; i < chars.length; i++) {
                        if (my !== epoch) return;
                        d.textContent += chars[i];
                        if (i % 4 === 0) scrollLog();
                        await sleep(sp);
                    }
                    scrollLog();
                }).catch(() => {});
                return chain;
            }

            function clearLog() {
                epoch++;
                chain = Promise.resolve();
                el.log.textContent = '';
                el.log.appendChild(cursor);
            }

            function boot(isReset) {
                say(isReset ? '[ SYSTEM ] Rebooting CODE THE THANKS AI...' : '[ SYSTEM ] Initializing CODE THE THANKS AI...', 'sys', 9);
                if (ctx) {
                    say('[ OK ] AI ENGINE STATUS: ONLINE (client-side simulation)', 'ok', 8);
                    say('[ OK ] Input interface connected.', 'ok', 8);
                    say('[ OK ] Render engine ready · canvas 1200x675 · export PNG', 'ok', 8);
                } else {
                    say('[ ERROR ] Canvas 2D is not supported by this browser.', 'err', 8);
                }
                say('> Awaiting input. Fill the form, then press INITIALIZE AI GENERATOR.', 'dim', 8);
            }

            function setProgress(p, state) {
                const v = Math.max(0, Math.min(100, Math.round(p)));
                el.bar.style.width = v + '%';
                el.pct.textContent = v + '%';
                el.progTrack.setAttribute('aria-valuenow', String(v));
                el.bar.classList.toggle('err', state === 'err');
                const labels = {
                    idle: 'IDLE',
                    run: 'PROCESSING',
                    ok: 'SUCCESS',
                    err: 'FAILED'
                };
                el.progLabel.textContent = 'STATUS: ' + (labels[state] || 'IDLE');
            }

            /* ------------------------------------------------------------------
             * Trạng thái nút / hệ thống
             * ------------------------------------------------------------------ */
            function updateUi() {
                el.btnGen.disabled = busy || !ctx;
                el.btnGen.textContent = busy ? '⏳ PROCESSING...' : '▶ INITIALIZE AI GENERATOR';
                el.btnReset.disabled = busy;
                el.btnDl.disabled = busy || !hasCard;
                el.btnRegen.disabled = busy || !hasCard;
                el.btnFull.disabled = busy || !hasCard;
                el.stRender.textContent = !ctx ? 'UNSUPPORTED' : (busy ? 'BUSY' : 'READY');
                el.stRender.className = 'st-v ' + (!ctx ? 'bad' : (busy ? 'busy' : 'ok'));
            }

            /* ------------------------------------------------------------------
             * Form
             * ------------------------------------------------------------------ */
            function updateCount() {
                const len = el.wish.value.length;
                const left = CFG.wishMax - len;
                el.count.textContent = len + '/' + CFG.wishMax + ' · còn ' + left;
                el.count.className = 'count' + (left <= 0 ? ' full' : (left <= 15 ? ' warn' : ''));
            }

            function setError(which, msg) {
                const field = which === 'name' ? el.fieldName : el.fieldWish;
                const out = which === 'name' ? el.errName : el.errWish;
                out.textContent = msg || '';
                field.classList.toggle('invalid', !!msg);
                if (msg) {
                    field.classList.remove('shake');
                    void field.offsetWidth;
                    field.classList.add('shake');
                }
            }

            function readInputs() {
                const anonymous = el.anon.checked;
                const name = anonymous ? 'Ẩn danh' : sanitize(el.name.value, CFG.nameMax);
                const wish = sanitize(el.wish.value, CFG.wishMax).replace(/`/g, "'");
                let ok = true;
                setError('name', '');
                setError('wish', '');

                if (!anonymous && !name) {
                    setError('name', 'Vui lòng nhập tên hoặc chọn “Gửi ẩn danh”.');
                    ok = false;
                }
                if (wish.length < CFG.wishMin) {
                    setError('wish', 'Vui lòng nhập lời chúc (tối thiểu ' + CFG.wishMin + ' ký tự).');
                    ok = false;
                }
                if (!ok) {
                    say('[ ERROR ] Missing required parameters. Check sender_name / gratitude_message.', 'err', 6);
                    showToast('Thiếu thông tin: hãy kiểm tra tên và lời chúc.', 'error');
                    (el.fieldName.classList.contains('invalid') ? el.name : el.wish).focus();
                    return null;
                }
                return {
                    name,
                    wish,
                    anonymous
                };
            }

            el.wish.addEventListener('input', () => {
                updateCount();
                setError('wish', '');
            });
            el.name.addEventListener('input', () => setError('name', ''));
            el.anon.addEventListener('change', () => {
                el.name.disabled = el.anon.checked;
                setError('name', '');
                say(el.anon.checked ? '> sender_name = "Ẩn danh"' : '> sender_name = <input>', 'sys-hi', 6);
            });

            el.btnRandom.addEventListener('click', () => {
                let i;
                do {
                    i = Math.floor(Math.random() * WISHES.length);
                } while (i === lastWishIdx && WISHES.length > 1);
                lastWishIdx = i;
                el.wish.value = WISHES[i].slice(0, CFG.wishMax);
                updateCount();
                setError('wish', '');
                say('> wish.random() → template #' + (i + 1) + ' loaded', 'sys', 6);
            });

            document.querySelectorAll('input[name="theme"]').forEach((r) => {
                r.addEventListener('change', () => {
                    const th = THEMES[selectedThemeKey()];
                    applyTheme(th);
                    say('> config.theme = "' + th.label + '"', 'sys', 6);
                    if (hasCard) say('[ INFO ] Theme changed. Press REGENERATE to apply.', 'warn', 6);
                });
            });

            el.form.addEventListener('keydown', (e) => {
                if (e.key !== 'Enter' || e.isComposing) return;
                if (e.target === el.wish) {
                    e.preventDefault();
                    if (!e.shiftKey) generate(false);
                } else if (e.ctrlKey || e.metaKey) {
                    e.preventDefault();
                    generate(false);
                }
            });

            el.form.addEventListener('submit', (e) => {
                e.preventDefault();
                generate(false);
            });

            el.btnRegen.addEventListener('click', () => generate(true));
            el.btnReset.addEventListener('click', resetSystem);

            /* ------------------------------------------------------------------
             * Canvas: tiện ích vẽ
             * ------------------------------------------------------------------ */
            function rrect(x, y, w, h, r) {
                ctx.beginPath();
                ctx.moveTo(x + r, y);
                ctx.arcTo(x + w, y, x + w, y + h, r);
                ctx.arcTo(x + w, y + h, x, y + h, r);
                ctx.arcTo(x, y + h, x, y, r);
                ctx.arcTo(x, y, x + w, y, r);
                ctx.closePath();
            }

            function radial(x, y, r, color, alpha) {
                const g = ctx.createRadialGradient(x, y, 0, x, y, r);
                g.addColorStop(0, rgba(color, alpha));
                g.addColorStop(1, rgba(color, 0));
                ctx.fillStyle = g;
                ctx.fillRect(x - r, y - r, r * 2, r * 2);
            }

            function wrapText(text, maxW) {
                const lines = [];
                let cur = '';
                const words = text.split(' ');
                for (const w of words) {
                    let word = w;
                    while (ctx.measureText(word).width > maxW) {
                        const chars = Array.from(word);
                        let k = chars.length;
                        while (k > 1 && ctx.measureText(chars.slice(0, k).join('')).width > maxW) k--;
                        if (cur) {
                            lines.push(cur);
                            cur = '';
                        }
                        lines.push(chars.slice(0, k).join(''));
                        word = chars.slice(k).join('');
                        if (!word) break;
                    }
                    if (!word) continue;
                    const test = cur ? cur + ' ' + word : word;
                    if (cur && ctx.measureText(test).width > maxW) {
                        lines.push(cur);
                        cur = word;
                    } else {
                        cur = test;
                    }
                }
                if (cur) lines.push(cur);
                return lines;
            }

            /* ---------- Họa tiết nền theo theme ---------- */
            function patMatrix(th, R) {
                ctx.save();
                ctx.font = '16px ' + MONO;
                ctx.textBaseline = 'top';
                const step = 22,
                    rows = Math.ceil(H / 18);
                for (let c = 0; c < Math.floor(W / step); c++) {
                    const x = c * step + 4;
                    const len = 6 + Math.floor(R() * 16);
                    const y0 = Math.floor(R() * rows) - Math.floor(len / 2);
                    for (let i = 0; i < len; i++) {
                        const y = (y0 + i) * 18;
                        if (y < -18 || y > H) continue;
                        const head = i === len - 1;
                        ctx.fillStyle = head ? 'rgba(255,255,255,0.6)' : rgba(th.accent, 0.04 + 0.4 * Math.pow(i / len, 1.5));
                        ctx.fillText(R() > 0.5 ? '1' : '0', x, y);
                    }
                }
                ctx.restore();
            }

            function patCyan(th, R) {
                ctx.save();
                ctx.strokeStyle = rgba(th.accent, 0.06);
                ctx.lineWidth = 1;
                ctx.beginPath();
                for (let x = 0; x <= W; x += 40) {
                    ctx.moveTo(x + 0.5, 0);
                    ctx.lineTo(x + 0.5, H);
                }
                for (let y = 0; y <= H; y += 40) {
                    ctx.moveTo(0, y + 0.5);
                    ctx.lineTo(W, y + 0.5);
                }
                ctx.stroke();

                const dirs = [
                    [1, 0],
                    [1, 1],
                    [0, 1],
                    [-1, 1],
                    [-1, 0],
                    [1, -1]
                ];
                ctx.lineWidth = 2;
                for (let i = 0; i < 18; i++) {
                    let x = Math.round(R() * W / 40) * 40;
                    let y = Math.round(R() * H / 40) * 40;
                    ctx.strokeStyle = rgba(th.accent, 0.16 + R() * 0.16);
                    ctx.beginPath();
                    ctx.moveTo(x, y);
                    const segs = 3 + Math.floor(R() * 3);
                    for (let s = 0; s < segs; s++) {
                        const d = dirs[Math.floor(R() * dirs.length)];
                        const l = (1 + Math.floor(R() * 3)) * 40;
                        x += d[0] * l;
                        y += d[1] * l;
                        ctx.lineTo(x, y);
                    }
                    ctx.stroke();
                    ctx.fillStyle = rgba(th.accent, 0.55);
                    ctx.beginPath();
                    ctx.arc(x, y, 5, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.strokeStyle = rgba(th.accent, 0.3);
                    ctx.beginPath();
                    ctx.arc(x, y, 11, 0, Math.PI * 2);
                    ctx.stroke();
                }
                ctx.restore();
            }

            function patSynth(th, R) {
                const hz = Math.round(H * 0.6);
                ctx.save();
                for (let i = 0; i < 70; i++) {
                    ctx.fillStyle = rgba('#ffffff', 0.2 + R() * 0.5);
                    ctx.beginPath();
                    ctx.arc(R() * W, R() * hz * 0.9, R() * 1.6 + 0.4, 0, Math.PI * 2);
                    ctx.fill();
                }
                // Mặt trời retro
                const cx = W / 2,
                    cy = hz - 10,
                    r = 175;
                const sg = ctx.createLinearGradient(0, cy - r, 0, hz);
                sg.addColorStop(0, rgba(th.glow2, 0.6));
                sg.addColorStop(1, rgba(th.accent, 0.6));
                ctx.save();
                ctx.beginPath();
                ctx.rect(0, 0, W, hz);
                ctx.clip();
                ctx.fillStyle = sg;
                ctx.beginPath();
                ctx.arc(cx, cy, r, 0, Math.PI * 2);
                ctx.fill();
                ctx.fillStyle = rgba(th.bg1, 0.95);
                const ys = [hz - 72, hz - 52, hz - 34, hz - 18],
                    hs = [3, 5, 7, 9];
                for (let k = 0; k < ys.length; k++) ctx.fillRect(cx - r, ys[k], r * 2, hs[k]);
                ctx.restore();
                // Đường chân trời
                ctx.strokeStyle = rgba(th.accent, 0.9);
                ctx.lineWidth = 2;
                ctx.shadowColor = th.accent;
                ctx.shadowBlur = 16;
                ctx.beginPath();
                ctx.moveTo(0, hz);
                ctx.lineTo(W, hz);
                ctx.stroke();
                ctx.shadowBlur = 0;
                // Mặt đất lưới
                ctx.fillStyle = rgba(th.bg1, 0.55);
                ctx.fillRect(0, hz, W, H - hz);
                ctx.strokeStyle = rgba(th.accent, 0.35);
                ctx.lineWidth = 1;
                ctx.beginPath();
                for (let i = -24; i <= 24; i++) {
                    ctx.moveTo(W / 2 + i * 16, hz);
                    ctx.lineTo(W / 2 + i * 16 * 9, H);
                }
                for (let k = 1; k <= 12; k++) {
                    const y = hz + Math.pow(k / 12, 2) * (H - hz);
                    ctx.moveTo(0, y);
                    ctx.lineTo(W, y);
                }
                ctx.stroke();
                ctx.restore();
            }

            function patGold(th, R) {
                ctx.save();
                // Tia sáng chéo
                for (let i = 0; i < 6; i++) {
                    const x1 = 200 + i * 230 + R() * 60;
                    ctx.fillStyle = rgba(th.accent, 0.025 + R() * 0.03);
                    ctx.beginPath();
                    ctx.moveTo(0, 0);
                    ctx.lineTo(x1, H);
                    ctx.lineTo(x1 + 70 + R() * 90, H);
                    ctx.closePath();
                    ctx.fill();
                }
                // Lưới lục giác
                const r = 30,
                    hh = Math.sqrt(3) * r;
                ctx.strokeStyle = rgba(th.accent, 0.08);
                ctx.lineWidth = 1;
                ctx.beginPath();
                for (let col = 0; col * 1.5 * r < W + r; col++) {
                    const cx = col * 1.5 * r;
                    const off = (col % 2) * hh / 2;
                    for (let cy = off - hh; cy < H + hh; cy += hh) {
                        for (let k = 0; k < 6; k++) {
                            const a = Math.PI / 3 * k;
                            const px = cx + r * Math.cos(a),
                                py = cy + r * Math.sin(a);
                            if (k === 0) ctx.moveTo(px, py);
                            else ctx.lineTo(px, py);
                        }
                        ctx.closePath();
                    }
                }
                ctx.stroke();
                // Bokeh
                for (let i = 0; i < 24; i++) {
                    radial(R() * W, R() * H, 16 + R() * 40, i % 3 ? th.accent : th.glow2, 0.08 + R() * 0.14);
                }
                // Lấp lánh
                ctx.fillStyle = rgba('#ffffff', 0.7);
                for (let i = 0; i < 14; i++) {
                    const x = R() * W,
                        y = R() * H,
                        s = 5 + R() * 9;
                    ctx.beginPath();
                    ctx.moveTo(x, y - s);
                    ctx.lineTo(x + s * 0.2, y - s * 0.2);
                    ctx.lineTo(x + s, y);
                    ctx.lineTo(x + s * 0.2, y + s * 0.2);
                    ctx.lineTo(x, y + s);
                    ctx.lineTo(x - s * 0.2, y + s * 0.2);
                    ctx.lineTo(x - s, y);
                    ctx.lineTo(x - s * 0.2, y - s * 0.2);
                    ctx.closePath();
                    ctx.fill();
                }
                ctx.restore();
            }

            function patBlue(th, R) {
                ctx.save();
                ctx.fillStyle = rgba(th.accent, 0.14);
                for (let x = 18; x < W; x += 36)
                    for (let y = 18; y < H; y += 36) ctx.fillRect(x, y, 2, 2);
                const nodes = [];
                for (let i = 0; i < 42; i++) nodes.push([R() * W, R() * H, 2 + R() * 2.5]);
                ctx.lineWidth = 1;
                for (let i = 0; i < nodes.length; i++) {
                    for (let j = i + 1; j < nodes.length; j++) {
                        const dx = nodes[i][0] - nodes[j][0],
                            dy = nodes[i][1] - nodes[j][1];
                        const d = Math.sqrt(dx * dx + dy * dy);
                        if (d < 170) {
                            ctx.strokeStyle = rgba(th.glow2, 0.22 * (1 - d / 170));
                            ctx.beginPath();
                            ctx.moveTo(nodes[i][0], nodes[i][1]);
                            ctx.lineTo(nodes[j][0], nodes[j][1]);
                            ctx.stroke();
                        }
                    }
                }
                for (const n of nodes) {
                    radial(n[0], n[1], 14, th.accent, 0.35);
                    ctx.fillStyle = rgba(th.glow2, 0.9);
                    ctx.beginPath();
                    ctx.arc(n[0], n[1], n[2], 0, Math.PI * 2);
                    ctx.fill();
                }
                ctx.restore();
            }

            const PATTERNS = {
                matrix: patMatrix,
                cyan: patCyan,
                synth: patSynth,
                gold: patGold,
                blue: patBlue
            };

            /* ---------- Thành phần chung của thiệp ---------- */
            function drawParticles(th, R) {
                ctx.save();
                for (let i = 0; i < 46; i++) {
                    const x = R() * W,
                        y = R() * H,
                        r = 1 + R() * 2.2;
                    const c = i % 2 ? th.accent : th.glow2;
                    ctx.fillStyle = rgba(c, 0.08);
                    ctx.beginPath();
                    ctx.arc(x, y, r * 3.2, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.fillStyle = rgba(c, 0.5 + R() * 0.4);
                    ctx.beginPath();
                    ctx.arc(x, y, r, 0, Math.PI * 2);
                    ctx.fill();
                }
                ctx.restore();
            }

            function drawFrame(th) {
                ctx.save();
                ctx.strokeStyle = rgba(th.accent, 0.28);
                ctx.lineWidth = 1.5;
                ctx.strokeRect(24, 24, W - 48, H - 48);
                ctx.strokeStyle = th.accent;
                ctx.lineWidth = 4;
                ctx.lineCap = 'square';
                ctx.shadowColor = th.accent;
                ctx.shadowBlur = 14;
                const o = 24,
                    L = 46;
                ctx.beginPath();
                ctx.moveTo(o, o + L);
                ctx.lineTo(o, o);
                ctx.lineTo(o + L, o);
                ctx.moveTo(W - o - L, o);
                ctx.lineTo(W - o, o);
                ctx.lineTo(W - o, o + L);
                ctx.moveTo(o, H - o - L);
                ctx.lineTo(o, H - o);
                ctx.lineTo(o + L, H - o);
                ctx.moveTo(W - o - L, H - o);
                ctx.lineTo(W - o, H - o);
                ctx.lineTo(W - o, H - o - L);
                ctx.stroke();
                ctx.restore();
            }

            function drawHeader(th, year) {
                ctx.save();
                ctx.textBaseline = 'alphabetic';
                ctx.textAlign = 'left';
                ctx.font = '800 22px ' + MONO;
                ctx.fillStyle = th.accent;
                ctx.shadowColor = th.accent;
                ctx.shadowBlur = 12;
                ctx.fillText('</> CODE THE THANKS', 64, 72);
                ctx.shadowBlur = 0;

                // Nhãn ngày 20/11
                const label = '20/11/' + year;
                ctx.font = '700 20px ' + MONO;
                const tw = ctx.measureText(label).width;
                const cw = tw + 40,
                    cx = W - 64 - cw,
                    cy = 46,
                    ch = 38;
                rrect(cx, cy, cw, ch, 19);
                ctx.fillStyle = rgba(th.accent, 0.14);
                ctx.fill();
                ctx.strokeStyle = rgba(th.accent, 0.7);
                ctx.lineWidth = 1.5;
                ctx.stroke();
                ctx.fillStyle = th.main;
                ctx.textAlign = 'center';
                ctx.fillText(label, cx + cw / 2, cy + 26);

                // Đường kẻ
                const lg = ctx.createLinearGradient(64, 0, W - 64, 0);
                lg.addColorStop(0, rgba(th.accent, 0.8));
                lg.addColorStop(1, rgba(th.accent, 0));
                ctx.fillStyle = lg;
                ctx.fillRect(64, 94, W - 128, 1.5);

                // Tiêu đề
                ctx.textAlign = 'center';
                ctx.font = '700 40px ' + SANS;
                ctx.fillStyle = th.main;
                ctx.shadowColor = th.accent;
                ctx.shadowBlur = 24;
                ctx.fillText('TRI ÂN NGÀY NHÀ GIÁO VIỆT NAM', W / 2, 146);
                ctx.shadowBlur = 0;
                ctx.font = '400 17px ' + MONO;
                ctx.fillStyle = th.punc;
                ctx.fillText('// Compile your gratitude into a beautiful memory.', W / 2, 177);
                ctx.restore();
            }

            function drawCodeWindow(name, wish, th) {
                const wx = 96,
                    wy = 200,
                    ww = W - 192,
                    wh = 410,
                    tb = 40;
                ctx.save();

                // Khung cửa sổ
                ctx.shadowColor = th.accent;
                ctx.shadowBlur = 34;
                rrect(wx, wy, ww, wh, 14);
                ctx.fillStyle = 'rgba(4,8,14,0.84)';
                ctx.fill();
                ctx.shadowBlur = 0;
                ctx.lineWidth = 2;
                ctx.strokeStyle = rgba(th.accent, 0.55);
                ctx.stroke();

                // Thanh tiêu đề
                ctx.save();
                rrect(wx, wy, ww, wh, 14);
                ctx.clip();
                ctx.fillStyle = 'rgba(255,255,255,0.05)';
                ctx.fillRect(wx, wy, ww, tb);
                ctx.fillStyle = rgba(th.accent, 0.25);
                ctx.fillRect(wx, wy + tb, ww, 1);
                ctx.fillStyle = 'rgba(255,255,255,0.03)';
                ctx.fillRect(wx, wy + tb + 1, 64, wh);
                ctx.restore();

                ['#ff5f56', '#ffbd2e', '#27c93f'].forEach((c, i) => {
                    ctx.beginPath();
                    ctx.fillStyle = c;
                    ctx.arc(wx + 26 + i * 22, wy + tb / 2, 6.5, 0, Math.PI * 2);
                    ctx.fill();
                });
                ctx.textBaseline = 'alphabetic';
                ctx.textAlign = 'center';
                ctx.font = '400 15px ' + MONO;
                ctx.fillStyle = th.punc;
                ctx.fillText('gratitude_' + th.key + '.js', wx + ww / 2, wy + tb / 2 + 5);

                // --- Nội dung code ---
                const FS = 22,
                    LH = 32;
                const codeX = wx + 84;
                ctx.font = '400 ' + FS + 'px ' + MONO;
                const ind = ctx.measureText('  ').width;
                const rightLimit = wx + ww - 44;
                let y = wy + tb + 24 + 20;
                let ln = 1;

                function gutter() {
                    ctx.save();
                    ctx.textAlign = 'right';
                    ctx.font = '400 14px ' + MONO;
                    ctx.fillStyle = rgba(th.punc, 0.55);
                    ctx.fillText(String(ln), wx + 50, y);
                    ctx.restore();
                    ln++;
                }

                function seg(parts, x) {
                    ctx.textAlign = 'left';
                    let cx = x;
                    for (const p of parts) {
                        ctx.font = p.f || ('400 ' + FS + 'px ' + MONO);
                        ctx.fillStyle = p.c;
                        if (p.g) {
                            ctx.shadowColor = p.c;
                            ctx.shadowBlur = p.g;
                        }
                        ctx.fillText(p.t, cx, y);
                        ctx.shadowBlur = 0;
                        cx += ctx.measureText(p.t).width;
                    }
                    return cx;
                }

                // Dòng 1
                gutter();
                seg([{
                    t: 'const ',
                    c: th.kw
                }, {
                    t: 'thiep ',
                    c: th.main
                }, {
                    t: '= {',
                    c: th.punc
                }], codeX);
                y += LH;

                // Dòng 2
                gutter();
                seg([{
                    t: 'nguoiNhan: ',
                    c: th.prop
                }, {
                    t: '"Quý Thầy Cô"',
                    c: th.str
                }, {
                    t: ',',
                    c: th.punc
                }], codeX + ind);
                y += LH;

                // Dòng 3: tên người gửi (nổi bật, tự co cỡ chữ)
                const availName = rightLimit - (codeX + ind);
                let nf = 32;
                for (; nf >= 18; nf--) {
                    ctx.font = '400 ' + FS + 'px ' + MONO;
                    const kw = ctx.measureText('nguoiGui: "').width;
                    ctx.font = '800 ' + nf + 'px ' + MONO;
                    const nw = ctx.measureText(name).width;
                    ctx.font = '400 ' + FS + 'px ' + MONO;
                    const tail = ctx.measureText('",').width;
                    if (kw + nw + tail <= availName) break;
                }
                const nameLH = Math.round(nf * 1.3) + 6;
                y += Math.max(0, nameLH - LH) * 0.6;
                gutter();
                seg([{
                        t: 'nguoiGui: ',
                        c: th.prop
                    },
                    {
                        t: '"',
                        c: th.str
                    },
                    {
                        t: name,
                        c: th.accent,
                        f: '800 ' + nf + 'px ' + MONO,
                        g: 14
                    },
                    {
                        t: '"',
                        c: th.str
                    },
                    {
                        t: ',',
                        c: th.punc
                    }
                ], codeX + ind);
                y += nameLH;

                // Dòng 4: mở chuỗi lời chúc
                gutter();
                seg([{
                    t: 'loiChuc: ',
                    c: th.prop
                }, {
                    t: '`',
                    c: th.str
                }], codeX + ind);
                y += LH;

                // Lời chúc: tự xuống dòng, tự giảm cỡ chữ
                const wishX = codeX + ind * 2;
                const maxW = rightLimit - wishX;
                const bottomLimit = wy + wh - 20;
                const fixedAfter = LH * 2; // dòng đóng chuỗi + dòng "};"
                const avail = bottomLimit - y - fixedAfter;
                let fs2 = 28,
                    lines, lh2;
                for (;; fs2--) {
                    ctx.font = '400 ' + fs2 + 'px ' + MONO;
                    lines = wrapText(wish, maxW);
                    lh2 = Math.round(fs2 * 1.45);
                    if (lines.length * lh2 <= avail || fs2 <= 15) break;
                }
                if (lines.length * lh2 > avail) {
                    const maxLines = Math.max(1, Math.floor(avail / lh2));
                    lines = lines.slice(0, maxLines);
                    const last = Array.from(lines[maxLines - 1]);
                    lines[maxLines - 1] = last.slice(0, Math.max(0, last.length - 1)).join('') + '…';
                }
                for (const line of lines) {
                    gutter();
                    ctx.textAlign = 'left';
                    ctx.font = '400 ' + fs2 + 'px ' + MONO;
                    ctx.fillStyle = th.str;
                    ctx.fillText(line, wishX, y);
                    y += lh2;
                }

                // Đóng chuỗi + đóng object
                gutter();
                seg([{
                    t: '`',
                    c: th.str
                }, {
                    t: ',',
                    c: th.punc
                }], codeX + ind);
                y += LH;
                gutter();
                const endX = seg([{
                    t: '};',
                    c: th.punc
                }], codeX);
                ctx.fillStyle = th.accent;
                ctx.shadowColor = th.accent;
                ctx.shadowBlur = 10;
                ctx.fillRect(endX + 10, y - 20, 12, 25);
                ctx.shadowBlur = 0;

                ctx.restore();
                return {
                    fontSize: fs2,
                    lines: lines.length,
                    nameSize: nf
                };
            }

            function drawFooter(th, year) {
                ctx.save();
                ctx.textBaseline = 'alphabetic';
                ctx.font = '400 14px ' + MONO;
                ctx.textAlign = 'left';
                ctx.fillStyle = rgba(th.punc, 0.9);
                ctx.fillText('// compiled by CODE THE THANKS · AI Greeting Card Generator', 64, 642);
                ctx.textAlign = 'right';
                ctx.fillStyle = th.accent;
                ctx.fillText('● COMPILED SUCCESSFULLY · ' + year, W - 64, 642);
                ctx.restore();
            }

            function drawCard(name, wish, th, seed) {
                const R = rng(seed);
                const year = new Date().getFullYear();

                ctx.setTransform(1, 0, 0, 1, 0, 0);
                ctx.globalAlpha = 1;
                ctx.shadowBlur = 0;
                ctx.shadowColor = 'transparent';
                ctx.clearRect(0, 0, W, H);

                // Nền gradient + ánh sáng
                const bg = ctx.createLinearGradient(0, 0, W, H);
                bg.addColorStop(0, th.bg1);
                bg.addColorStop(1, th.bg2);
                ctx.fillStyle = bg;
                ctx.fillRect(0, 0, W, H);
                radial(0, 0, 560, th.accent, 0.22);
                radial(W, H, 620, th.glow2, 0.2);
                radial(W * 0.5, H * 0.45, 420, th.accent, 0.07);

                PATTERNS[th.key](th, R);
                drawParticles(th, R);

                // Vignette
                const vg = ctx.createRadialGradient(W / 2, H / 2, 240, W / 2, H / 2, 780);
                vg.addColorStop(0, 'rgba(0,0,0,0)');
                vg.addColorStop(1, 'rgba(0,0,0,0.6)');
                ctx.fillStyle = vg;
                ctx.fillRect(0, 0, W, H);

                drawFrame(th);
                drawHeader(th, year);
                const info = drawCodeWindow(name, wish, th);
                drawFooter(th, year);
                return info;
            }

            async function ensureFonts() {
                if (!document.fonts || !document.fonts.load) return;
                const sample = 'Tri ân thầy cô ẩệơưđ';
                const jobs = [
                    '400 22px "JetBrains Mono"', '700 22px "JetBrains Mono"', '800 22px "JetBrains Mono"', '700 40px "Space Grotesk"'
                ].map((f) => document.fonts.load(f, sample).catch(() => null));
                await Promise.race([Promise.all(jobs), sleep(2500)]);
            }

            /* ------------------------------------------------------------------
             * Quy trình mô phỏng AI + kết xuất thiệp
             * ------------------------------------------------------------------ */
            async function generate(isRegen) {
                if (busy || !ctx) return;
                const data = readInputs();
                if (!data) return;

                const th = THEMES[selectedThemeKey()];
                const {
                    name,
                    wish
                } = data;
                const words = wish.split(' ').length;
                const fast = isRegen || reduceMotion.matches;
                const sp = fast ? 4 : 12;
                const wait = fast ? 90 : 300;
                const t0 = performance.now();

                busy = true;
                hasCard = false;
                updateUi();
                applyTheme(th);
                el.stage.classList.remove('has-card', 'done');
                el.stage.classList.add('processing');
                setChip('COMPILING…', 'busy');
                el.metaTime.textContent = '…';
                setProgress(2, 'run');

                let info = null;
                let step = 0;

                try {
                    await say('', 'sys', 0);
                    await say('> ai.generate --mode greeting-card --theme ' + th.key + (isRegen ? ' --regenerate' : ''), 'cmd', sp);

                    const steps = [{
                            t: '[01/06] Receiving user information...',
                            s: () => '        ↳ sender="' + name + '" · ' + wish.length + ' chars'
                        },
                        {
                            t: '[02/06] Analyzing gratitude message...',
                            s: () => '        ↳ words=' + words + ' · chars=' + wish.length + '/' + CFG.wishMax
                        },
                        {
                            t: '[03/06] Processing emotional context...',
                            s: () => '        ↳ context="Ngày Nhà giáo Việt Nam 20/11"'
                        },
                        {
                            t: '[04/06] Selecting visual theme...',
                            s: () => '        ↳ theme=' + th.label + ' · accent=' + th.accent
                        },
                        {
                            t: '[05/06] Compiling greeting card design...',
                            s: () => '        ↳ layout=code-window · typography=auto-fit'
                        },
                        {
                            t: '[06/06] Rendering canvas 1200x675...',
                            s: () => '        ↳ font=' + info.fontSize + 'px · lines=' + info.lines + ' · name=' + info.nameSize + 'px'
                        }
                    ];

                    for (step = 0; step < steps.length; step++) {
                        setProgress(step / 6 * 100 + 3, 'run');
                        await say(steps[step].t, 'step', sp);
                        if (step === steps.length - 1) {
                            await ensureFonts();
                            const seed = (Date.now() ^ hashStr(name + '|' + wish)) >>> 0;
                            info = drawCard(name, wish, th, seed);
                        }
                        await say(steps[step].s(), 'sub', Math.max(2, sp - 6));
                        setProgress((step + 1) / 6 * 100 - (step === 5 ? 1 : 0), 'run');
                        await sleep(wait);
                    }

                    const secs = ((performance.now() - t0) / 1000).toFixed(2);
                    lastFile = 'thiep_' + slugify(data.anonymous ? '' : name) + '.png';
                    hasCard = true;
                    setProgress(100, 'ok');
                    el.stage.classList.remove('processing');
                    el.stage.classList.add('has-card', 'done');
                    el.terminal.classList.remove('glow-done');
                    void el.terminal.offsetWidth;
                    el.terminal.classList.add('glow-done');
                    setTimeout(() => el.stage.classList.remove('done'), 1700);
                    el.metaFile.textContent = lastFile;
                    el.metaTime.textContent = secs + 's';
                    setChip('COMPILED · READY', 'ok');
                    await say('[ SUCCESS ] Greeting card generated successfully!', 'done', sp);
                    await say('[ SYSTEM ] Completed in ' + secs + 's. Ready to export PNG.', 'ok', sp);
                } catch (err) {
                    hasCard = false;
                    el.stage.classList.remove('processing', 'has-card');
                    setProgress(Math.max(5, step / 6 * 100), 'err');
                    setChip('COMPILE FAILED', 'err');
                    el.metaTime.textContent = '—';
                    say('[ ERROR ] Render exception: ' + (err && err.message ? err.message : 'unknown'), 'err', 6);
                    showToast('Kết xuất thất bại, vui lòng thử lại.', 'error');
                } finally {
                    busy = false;
                    updateUi();
                }
            }

            /* ------------------------------------------------------------------
             * Tải PNG / xem lớn
             * ------------------------------------------------------------------ */
            function downloadUrl(url, revoke) {
                const a = document.createElement('a');
                a.href = url;
                a.download = lastFile;
                a.rel = 'noopener';
                document.body.appendChild(a);
                a.click();
                a.remove();
                if (revoke) setTimeout(() => URL.revokeObjectURL(url), 5000);
            }

            function onDownloaded() {
                showToast('Đã tải thiệp: ' + lastFile, 'ok');
                say('[ OK ] Export PNG 1200x675 → ' + lastFile, 'ok', 6);
            }

            function downloadCard() {
                if (busy || !hasCard) return;
                const canDownloadAttr = 'download' in document.createElement('a');
                if (!canDownloadAttr) {
                    openLightbox('Trình duyệt không hỗ trợ tải trực tiếp: nhấn giữ (hoặc chuột phải) vào ảnh để lưu.');
                    return;
                }
                try {
                    if (el.canvas.toBlob) {
                        el.canvas.toBlob((blob) => {
                            try {
                                if (blob) {
                                    downloadUrl(URL.createObjectURL(blob), true);
                                } else {
                                    downloadUrl(el.canvas.toDataURL('image/png'), false);
                                }
                                onDownloaded();
                            } catch (e2) {
                                openLightbox('Không thể tải tự động: nhấn giữ (hoặc chuột phải) vào ảnh để lưu.');
                            }
                        }, 'image/png');
                    } else {
                        downloadUrl(el.canvas.toDataURL('image/png'), false);
                        onDownloaded();
                    }
                } catch (err) {
                    showToast('Không thể xuất PNG. Hãy thử xem lớn rồi lưu ảnh.', 'error');
                    say('[ ERROR ] Export failed: ' + (err && err.message ? err.message : 'unknown'), 'err', 6);
                }
            }

            function openLightbox(hint) {
                if (!hasCard) return;
                const show = (url) => {
                    if (lbUrl && lbUrl.indexOf('blob:') === 0) URL.revokeObjectURL(lbUrl);
                    lbUrl = url;
                    el.lbImg.src = url;
                    el.lbImg.alt = 'Thiệp tri ân - ' + lastFile;
                    el.lbHint.textContent = hint || 'Nhấn ra ngoài hoặc phím Esc để đóng.';
                    el.lb.hidden = false;
                    document.body.classList.add('no-scroll');
                    el.lbClose.focus();
                };
                if (el.canvas.toBlob) {
                    el.canvas.toBlob((b) => show(b ? URL.createObjectURL(b) : el.canvas.toDataURL('image/png')), 'image/png');
                } else {
                    show(el.canvas.toDataURL('image/png'));
                }
            }

            function closeLightbox() {
                if (el.lb.hidden) return;
                el.lb.hidden = true;
                document.body.classList.remove('no-scroll');
                el.lbImg.removeAttribute('src');
                if (lbUrl && lbUrl.indexOf('blob:') === 0) URL.revokeObjectURL(lbUrl);
                lbUrl = '';
                el.btnFull.focus();
            }

            el.btnDl.addEventListener('click', downloadCard);
            el.btnFull.addEventListener('click', () => openLightbox());
            el.lbClose.addEventListener('click', closeLightbox);
            el.lb.addEventListener('click', (e) => {
                if (e.target !== el.lbImg) closeLightbox();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeLightbox();
            });

            /* ------------------------------------------------------------------
             * Reset hệ thống
             * ------------------------------------------------------------------ */
            function resetSystem() {
                if (busy) return;
                el.name.value = '';
                el.name.disabled = false;
                el.anon.checked = false;
                el.wish.value = '';
                const first = document.querySelector('input[name="theme"][value="matrix"]');
                if (first) first.checked = true;
                applyTheme(THEMES.matrix);
                updateCount();
                setError('name', '');
                setError('wish', '');
                if (ctx) {
                    ctx.setTransform(1, 0, 0, 1, 0, 0);
                    ctx.clearRect(0, 0, W, H);
                }
                hasCard = false;
                lastFile = '';
                lastWishIdx = -1;
                el.stage.classList.remove('has-card', 'processing', 'done');
                el.metaFile.textContent = 'output_buffer.png';
                el.metaTime.textContent = '—';
                setChip('WAITING FOR COMPILATION', '');
                setProgress(0, 'idle');
                clearLog();
                boot(true);
                updateUi();
                el.name.focus();
            }

            /* ------------------------------------------------------------------
             * Đồng hồ ngày giờ
             * ------------------------------------------------------------------ */
            function tickClock() {
                try {
                    const d = new Date();
                    const day = d.toLocaleDateString('vi-VN', {
                        weekday: 'long',
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });
                    const time = d.toLocaleTimeString('vi-VN', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });
                    el.clock.textContent = day + ' · ' + time;
                } catch (e2) {
                    /* giữ nguyên ngày do PHP in ra */ }
            }

            /* ------------------------------------------------------------------
             * Hiệu ứng nền: mưa mã (nhẹ, tạm dừng khi ẩn tab / giảm chuyển động)
             * ------------------------------------------------------------------ */
            const rain = {
                ctx: el.rain.getContext ? el.rain.getContext('2d') : null,
                fs: 16,
                cols: [],
                w: 0,
                h: 0,
                raf: 0,
                last: 0,
                on: false
            };
            const RAIN_CHARS = '01010101</>{};=+*#';

            function sizeRain() {
                rain.w = el.rain.width = window.innerWidth;
                rain.h = el.rain.height = window.innerHeight;
                const n = Math.ceil(rain.w / rain.fs);
                rain.cols = Array.from({
                    length: n
                }, () => Math.random() * rain.h / rain.fs);
            }

            function stepRain(ts) {
                rain.raf = requestAnimationFrame(stepRain);
                if (ts - rain.last < 70) return;
                rain.last = ts;
                const c = rain.ctx;
                c.globalCompositeOperation = 'destination-out';
                c.fillStyle = 'rgba(0,0,0,0.14)';
                c.fillRect(0, 0, rain.w, rain.h);
                c.globalCompositeOperation = 'source-over';
                c.font = rain.fs + 'px ' + MONO;
                c.fillStyle = rgba(currentTheme.accent, 0.9);
                for (let i = 0; i < rain.cols.length; i++) {
                    const ch = RAIN_CHARS.charAt(Math.floor(Math.random() * RAIN_CHARS.length));
                    c.fillText(ch, i * rain.fs, rain.cols[i] * rain.fs);
                    if (rain.cols[i] * rain.fs > rain.h && Math.random() > 0.975) rain.cols[i] = 0;
                    rain.cols[i]++;
                }
            }

            function startRain() {
                if (!rain.ctx || rain.on || reduceMotion.matches || document.hidden) return;
                rain.on = true;
                sizeRain();
                rain.raf = requestAnimationFrame(stepRain);
            }

            function stopRain() {
                rain.on = false;
                cancelAnimationFrame(rain.raf);
            }

            let resizeTimer = 0;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    if (rain.on) sizeRain();
                }, 200);
            });
            document.addEventListener('visibilitychange', () => {
                document.hidden ? stopRain() : startRain();
            });
            if (reduceMotion.addEventListener) {
                reduceMotion.addEventListener('change', () => {
                    reduceMotion.matches ? stopRain() : startRain();
                });
            }

            /* ------------------------------------------------------------------
             * Khởi động
             * ------------------------------------------------------------------ */
            if (!ctx) {
                el.stEngine.textContent = 'OFFLINE';
                el.stEngine.className = 'st-v bad';
            }
            applyTheme(THEMES[selectedThemeKey()]);
            updateCount();
            updateUi();
            tickClock();
            setInterval(() => {
                if (!document.hidden) tickClock();
            }, 1000);
            startRain();
            boot(false);
        })();
    </script>
</body>

</html>