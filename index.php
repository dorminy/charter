<?php
/**
 * Nashville Chart Builder (Single-file PHP module)
 * Copyright (C)2026 Mark Dorminy
 
 * - Setup fields: title, composer, key, time signature, tempo
 * - Aligned beats: fixed-width beat cells in HTML preview/export (monospace)
 * - Save/Load JSON, print chart
 *
 * Requirements: PHP 7.4+ (no DB needed)
  
This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.  If not, see <https://www.gnu.org/licenses/>.    
*/
    
function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Nashville Chart Builder</title>
  <style>
    :root{
      --bg:#0b1220; --panel:#101a31; --muted:#93a4c7; --text:#e8eefc;
      --accent:#6aa6ff; --danger:#ff6a7a; --border:#1f2b4a;
      --chip:#16254a; --shadow: 0 10px 30px rgba(0,0,0,.35);
      --radius:16px;
      --beatGuidePx: 64px;

      /* HTML preview sizing */
      --cellCh: 10ch;     /* beat cell width */
      --beats: 4;         /* beats per bar */
      --cols: 4;          /* bars per line */
      font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    }

    /* Prevent grid children from forcing overflow */
    *, *::before, *::after{ box-sizing:border-box; }

    body{
      margin:0;
      background:linear-gradient(180deg,#070b14 0%, #0b1220 40%, #070b14 100%);
      color:var(--text);
      overflow-x:hidden;
    }

    .wrap{ max-width:1100px; margin:0 auto; padding:24px; }
    h1{ font-size:24px; margin:0 0 12px; }
    .sub{ color:var(--muted); margin:0 0 20px; }

    .grid{
      display:grid;
      grid-template-columns: 1fr;
      gap:14px;
      min-width:0;
    }

    .card{
      background:rgba(16,26,49,.9);
      border:1px solid var(--border);
      border-radius:var(--radius);
      box-shadow:var(--shadow);
      min-width:0;
      max-width:100%;
    }

    .card header{
      padding:14px 16px;
      border-bottom:1px solid var(--border);
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
    }
    .card header h2{ margin:0; font-size:16px; }
    .card .content{ padding:16px; }

    .row{
      display:grid;
      grid-template-columns: 1fr 1fr;
      gap:12px;
      min-width:0;
    }
    .row3{
      display:grid;
      grid-template-columns: 2fr 1fr 1fr;
      gap:12px;
      min-width:0;
    }
    .row > div, .row3 > div{ min-width:0; }

    label{ display:block; font-size:12px; color:var(--muted); margin-bottom:6px;}
    input, select, textarea{
      width:100%; background:#0b142b; color:var(--text);
      border:1px solid var(--border); border-radius:12px;
      padding:10px 12px; box-sizing:border-box;
      outline:none;
    }
    textarea{ min-height:84px; resize:vertical; }
    input:focus, select:focus, textarea:focus{ border-color:var(--accent); }

    .btns{ display:flex; flex-wrap:wrap; gap:10px; }
    button{
      background:var(--chip); color:var(--text);
      border:1px solid var(--border); border-radius:12px;
      padding:10px 12px; cursor:pointer;
      transition: transform .05s ease, border-color .15s ease;
    }
    button:hover{ border-color:var(--accent); }
    button:active{ transform: translateY(1px); }
    .primary{ background:linear-gradient(180deg,#2b66ff 0%, #1d4ed8 100%); border-color:#2b66ff; }
    .danger{ background:rgba(255,106,122,.12); border-color:rgba(255,106,122,.35); }
    .muted{ color:var(--muted); font-size:12px; }

    .section{
      border:1px dashed rgba(147,164,199,.35);
      border-radius:14px; padding:12px; margin-bottom:12px;
      background: rgba(11,20,43,.45);
      min-width:0;
    }
    .section-top{
      display:flex; justify-content:space-between; align-items:center;
      gap:10px; margin-bottom:10px;
      min-width:0;
    }
    .section-top strong{ font-size:14px; }
    .pill{
      font-size:12px; color:var(--muted);
      background:rgba(22,37,74,.7);
      padding:6px 10px; border-radius:999px;
      border:1px solid var(--border);
    }

    .lines{ display:grid; gap:10px; min-width:0; }
    .line{
      display:grid;
      grid-template-columns: 120px 1fr;
      gap:10px;
      align-items:start;
      border:1px solid var(--border);
      border-radius:14px;
      padding:10px;
      background: rgba(11,20,43,.55);
      min-width:0;
    }
    .line .meta{ display:flex; flex-direction:column; gap:8px; min-width:0; }
    .measures{ display:grid; gap:8px; min-width:0; }

    .measures-grid{
      display:grid;
      gap:8px;
      grid-template-columns: repeat(var(--cols, 4), minmax(0,1fr));
      min-width:0;
    }
    .measure{
      border:1px solid rgba(147,164,199,.25);
      border-radius:12px;
      padding:10px;
      background: rgba(7,11,20,.45);
      min-width:0;
    }

    /* Larger, bolder chord text + monospace for consistent alignment */
    .measure textarea{
      min-height:72px;
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
      font-size:16px;
      font-weight:650;
      letter-spacing:0.2px;
      line-height:1.25;

      /* Optional beat guide */
      background-image: repeating-linear-gradient(
        to right,
        rgba(147,164,199,.10) 0,
        rgba(147,164,199,.10) 1px,
        transparent 1px,
        transparent var(--beatGuidePx, 64px)
      );
      background-size: var(--beatGuidePx, 64px) 100%;
      background-origin: content-box;
    }

    .footer-actions{ display:flex; flex-wrap:wrap; gap:10px; align-items:center; min-width:0; }

    /* -----------------------------
       OPTION B LAYOUT:
       Preview full width, then Export + Notes side-by-side
       ----------------------------- */
.split{
  display:flex;
  flex-direction:column;
  gap:14px;
  min-width:0;
}

/* FULL WIDTH */
.split-preview{
  width:100%;
  min-width:0;
}

/* NOTES FULL WIDTH + FLEX COLUMN */
.notes-container{
  display:flex;
  flex-direction:column;
  min-width:0;
}

      
   

#notes{
  flex:1;
  min-height:140px;
  resize:vertical;
}

    pre{
      background: #070b14; border:1px solid var(--border);
      border-radius: 14px; padding: 12px; overflow:auto;
      color: #dbe7ff; margin:0;
      font-size: 12px; line-height: 1.35;
      white-space: pre;
      max-width:100%;
      min-width:0;
    }

    /* -----------------------------
       HTML Preview Styles
       ----------------------------- */
    .preview{
      background: #070b14;
      border:1px solid var(--border);
      border-radius: 14px;
      padding: 12px;
      overflow:auto;          /* scroll INSIDE preview if needed */
      min-height: 120px;
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
      width:100%;
      max-width:100%;
      min-width:0;
    }
    .p-meta{
      display:flex;
      flex-wrap:wrap;
      gap:10px 14px;
      align-items:baseline;
      color: var(--muted);
      font-size:12px;
      margin-bottom:10px;
      font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    }
    .p-meta strong{ color: var(--text); }

    .p-sectionTitle{
      margin: 10px 0 6px;
      font-size: 12px;
      letter-spacing: .3px;
      text-transform: uppercase;
      color: rgba(232,238,252,.92);
      font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    }

    /* Full-width preview bars with refined spacing */
    .p-line{
      display: grid;
      grid-template-columns: repeat(var(--cols), minmax(0, 1fr));
      gap: clamp(8px, 2vw, 16px);
      margin-bottom: 2px;
      width: 100%;
      align-items:start;
    }

    .p-bar{
      display: grid;
      grid-template-columns: repeat(var(--beats), minmax(var(--cellCh), 1fr));
      gap: 0;
      border: 1px solid rgba(147,164,199,.20);
      border-radius: 12px;
      background: rgba(11,20,43,.40);
      overflow:hidden;
      width:100%;
    }

    .p-beat{
      padding: 6px 8px;
      min-height: 2.2em;
      border-right: 1px solid rgba(147,164,199,.15);
      display:flex;
      align-items:center;
      justify-content:center;
      white-space: pre;
      box-sizing:border-box;
      font-weight: 600;
      letter-spacing: 0.03em;
    }
    .p-beat:last-child{ border-right:none; }
    .p-dot{ color: rgba(147,164,199,.55); } /* for "." beats */

    .p-notesTitle{
      margin-top: 12px;
      font-size: 12px;
      letter-spacing: .3px;
      text-transform: uppercase;
      color: rgba(232,238,252,.92);
      font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    }
    .p-notes{
      color: rgba(219,231,255,.92);
      font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
      font-size: 13px;
      white-space: pre-wrap;
      margin-top: 6px;
      display:block !important; 
    }

    @media (max-width: 900px){
      .row, .row3{ grid-template-columns: 1fr; }
      .line{ grid-template-columns: 1fr; }

      /* Option B responsive: bottom row stacks */
      .split{ grid-template-columns: 1fr; }
      .split-bottom{ grid-template-columns: 1fr; }
    }
      
 button + button {
  margin-left: 4px;
}     

/* -----------------------------
   PRINT (Clean Session Sheet)
   ----------------------------- */
@media print {

  body{
    background:white !important;
    color:black !important;
    font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    line-height: 1.05;   /* tighter vertical rhythm */ 
  }

  .wrap{
    max-width:100%;
    padding:0;
  }

  /*  REMOVE UI / INSTRUCTIONS / LABELS */
  #setupCard,
  #builderCard header,
  #sections,
  .footer-actions,
  label,
  button,
  .muted,
  .sub {
    display:none !important;
  }

  /*  REMOVE MAIN APP TITLE */
  h1{
    display:none !important;
  }

  /*  SHOW ONLY PREVIEW AREA */
  .split,
  .split-preview{
    display:block !important;
    width:100% !important;
  }

  .split-bottom{
    display:none !important;
  }

 /*  REMOVE editor notes completely */
.notes-container{
  display:none !important;
}

  /*  CLEAN PREVIEW CONTAINER */
  .preview{
    border:none !important;
    box-shadow:none !important;
    background:white !important;
    padding:0;
    margin:0;
    overflow:visible;
  }

  /*  REMOVE ALL VISUAL BOXES */
  .p-bar,
  .card,
  .measure{
    border:none !important;
    box-shadow:none !important;
    background:none !important;
  }

   .p-bar{
  		display:grid;
  		grid-template-columns: repeat(var(--beats), minmax(1.6em, 1fr));
  		width:100%;
        border-right: 1px solid rgba(0,0,0,1);
	}

  
.p-beat{
  border:none !important;  

  font-size:12pt;
  text-align:center;

  min-height: 0 !important;   /* CRITICAL: remove tall cells */
  height: auto !important;

  padding: 2px 3px !important; /* no vertical padding */
  line-height: 1.0 !important;

  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;

  display:flex;
  align-items:center;
  justify-content:center;
}

  /*  GRID FITTED TO PAGE WIDTH */
  .p-line{
    display:grid;
    grid-template-columns: repeat(var(--cols), 1fr);
    gap: 2px;
    margin-bottom: 0px;
    page-break-inside: avoid;
    line-height: 1.0;  
  }

/*  META CONTAINER */
.p-meta{
  display:block;
  margin-bottom:6px;
}

/*  TITLE LINE */
.p-title strong{
  font-size:18pt;
  font-weight:700;
  color: black !important;
  margin-bottom:2px;
}

/*  METADATA ROW (COLUMN GRID) */
.p-meta-row{
  display:grid;
  grid-template-columns: repeat(4, auto);
  gap: 8px 24px;
  margin-bottom: 2px;  
  font-size:11pt;
}

/*  INDIVIDUAL ITEMS */
.p-meta-item{
  white-space:nowrap;
}

  .p-meta strong{
    font-weight:700;
  }

  /*  SECTION LABELS CLEAN */
  .p-sectionTitle{
    margin:4px 0 2px;
    font-size:11pt;
    font-weight:bold;
    text-transform:none;
    line-height: 1.05; 
  }

/*  Default beat text */
.p-beat{
  font-size: 11pt;
  padding: 1px 4px;    /* tighter cell padding */
  line-height: 1.05;
}

/*  Medium-length chords */
.p-beat.long{
  font-size: 9.5pt;
}

/*  Very long chords */
.p-beat.xlong{
  font-size: 8.5pt;
}
    
    
    
  /*  NOTES */
  .p-notesTitle{
    margin-top:6px;
    font-size:11pt;
    font-weight:bold;
  }

  .p-notes{
    font-size:10pt;
    margin-top: 2px !important; 
  }

  /*  PAGE SETUP */
  @page{
    size: letter;
    margin: 0.5in;
  }

  /*  PREVENT BAD PAGE BREAKS */
  .p-line,
  .p-sectionTitle{
    break-inside: avoid;
  }

}  
      
  </style>
</head>
<body>
<div class="wrap">
  <h1>Nashville Chart Builder</h1>
  <p class="sub">Enter setup info, then dynamically build a Nashville-style chart. Preview/export uses fixed-width beat cells so chords align on rows.</p>

  <div class="grid">

    <section class="card" id="setupCard">
      <header>
        <h2>1) Setup</h2>
        <span class="pill">Metadata</span>
      </header>
      <div class="content">
        <div class="row">
          <div>
            <label for="title">Title</label>
            <input id="title" placeholder="Song title">
          </div>
          <div>
            <label for="composer">Composer</label>
            <input id="composer" placeholder="Composer / writer(s)">
          </div>
        </div>

        <div class="row3" style="margin-top:12px;">
          <div>
            <label for="key">Key</label>
            <input id="key" placeholder="e.g., G, Bb, F#m">
          </div>
          <div>
            <label for="timeSig">Time Signature</label>
            <input id="timeSig" placeholder="e.g., 4/4, 6/8">
          </div>
          <div>
            <label for="tempo">Tempo (BPM)</label>
            <input id="tempo" type="number" min="1" step="1" placeholder="e.g., 92">
          </div>
        </div>

        <div class="row" style="margin-top:12px;">
          <div>
            <label for="notation">Notation Mode</label>
            <select id="notation">
              <option value="numbers">Numbers (NNS)</option>
              <option value="letters">Letters (Chord names)</option>
              <option value="both">Both / Mixed</option>
            </select>
          </div>
          <div>
            <label for="barsPerLine">Bars per line (grid)</label>
            <select id="barsPerLine">
              <option value="4">4</option>
              <option value="2">2</option>
              <option value="8">8</option>
              <option value="3">3</option>
              <option value="6">6</option>
            </select>
          </div>
        </div>

        <div class="row3" style="margin-top:12px;">
          <div>
            <label for="cellWidth">Beat cell width (characters)</label>
            <select id="cellWidth">
              <option value="8">8 (wide)</option>
              <option value="10">10 (wider)</option>
              <option value="12">12 (extra wide)</option>
              <option value="6" selected>6 (compact)</option>
            </select>
          </div>
          <div>
            <label for="beatGuidePx">Beat guide spacing (px)</label>
            <select id="beatGuidePx">
              <option value="56">56</option>
              <option value="64" selected>64</option>
              <option value="72">72</option>
              <option value="80">80</option>
            </select>
          </div>
          <div>
            <label for="beatsOverride">Beats per bar (override)</label>
            <select id="beatsOverride">
              <option value="">Auto from time signature</option>
              <option value="2">2</option>
              <option value="3">3</option>
              <option value="4">4</option>
              <option value="6">6</option>
              <option value="8">8</option>
              <option value="12">12</option>
            </select>
          </div>
        </div>

        <div class="muted" style="margin-top:10px;">
          Enter one token per beat in each bar (separate with spaces). Examples (4/4):
          <code>1 . 4 .</code> 
          &nbsp; Push: <code>^4</code>
          &nbsp; Hold: <code>1~</code>
          &nbsp; Choke: <code>1!</code>
          &nbsp; Passing: <code>(6m 5)</code>
        </div>
      </div>
    </section>

    <section class="card" id="builderCard">
      <header>
        <h2>2) Build Chart</h2>
        <div class="btns">
          <button class="primary" id="addSectionBtn" type="button">+ Add Section</button>
          <button id="clearBtn" class="danger" type="button">Clear</button>
        </div>
      </header>
      <div class="content">
        <div id="sections"></div>

<div style="margin-top:10px;">
  <button id="addSectionBottomBtn" class="primary" type="button">
    + Add Section
  </button>
</div>

        <div class="footer-actions" style="margin-top:8px;">
          <button id="printBtn" type="button">Print Chart</button>
          <button id="downloadJsonBtn" type="button">Download JSON</button>

          <label style="margin:0; display:flex; align-items:center; gap:10px;">
            <span class="muted">Load JSON</span>
            <input id="loadJsonInput" type="file" accept="application/json" style="max-width:260px;">
          </label>
        </div>

        <!-- OPTION B: Preview full width, Export+Notes below -->
<div class="split" style="margin-top:14px;">

  <!--  FULL WIDTH PREVIEW -->
  <div class="split-preview">
    <label>Chart</label>
    <div id="htmlPreview" class="preview"></div>
  </div>

  <!--  NOTES (FULL WIDTH BELOW) -->
  <div class="notes-container">
    <label>Notes (optional)</label>
    <textarea id="notes" placeholder="Hits, cues, arrangement notes, lyrics cues..."></textarea>
    <div class="muted" style="margin-top:10px;">
      Everything stays in-browser until you download JSON.
    </div>
  </div>

</div>
          
          
      </div>
    </section>

  </div>
</div>

<script>
  // ---------- Data Model ----------
  const state = {
    meta: {
      title:"", composer:"", key:"", timeSig:"", tempo:"",
      notation:"numbers",
      barsPerLine:4,
      cellWidth: 10,
      beatGuidePx: 64,
      beatsOverride: ""
    },
    notes: "",
    sections: []
  };

  const defaultSectionTypes = ["Intro","Verse","Pre-Chorus","Chorus","Bridge","Tag","Outro","Solo","Turnaround","Vamp"];

  function newSection(name="Verse", bars=8) {
    return { id: crypto.randomUUID(), name, bars, lines: [] };
  }

  function ensureLines(section) {
    const cols = parseInt(state.meta.barsPerLine, 10) || 4;
    const totalBars = Math.max(1, parseInt(section.bars, 10) || 1);
    const neededLines = Math.ceil(totalBars / cols);

    while (section.lines.length < neededLines) {
      section.lines.push({
        id: crypto.randomUUID(),
        label: `Line ${section.lines.length + 1}`,
        measures: Array.from({length: cols}, () => ({ id: crypto.randomUUID(), text: "" }))
      });
    }
    while (section.lines.length > neededLines) section.lines.pop();

    section.lines.forEach((line, idx) => {
      const isLast = idx === neededLines - 1;
      const neededMeasures = isLast ? (totalBars - (cols * (neededLines - 1))) : cols;
      while (line.measures.length < neededMeasures) line.measures.push({ id: crypto.randomUUID(), text: "" });
      while (line.measures.length > neededMeasures) line.measures.pop();
    });
  }

  // ---------- Alignment helpers ----------
  function parseTimeSigBeats(ts) {
    const m = String(ts || "").trim().match(/^(\d+)\s*\/\s*(\d+)$/);
    if (!m) return 4;
    const top = parseInt(m[1], 10);
    return Number.isFinite(top) && top > 0 ? top : 4;
  }

  function effectiveBeatsPerBar() {
    const o = String(state.meta.beatsOverride || "").trim();
    if (o) {
      const n = parseInt(o, 10);
      if (Number.isFinite(n) && n > 0) return n;
    }
    return parseTimeSigBeats(state.meta.timeSig);
  }

  function splitIntoBeatTokens(raw, beats) {
    const s = String(raw || "").trim();
    if (!s) return Array.from({length: beats}, () => ".");
    let parts = s.includes("|") ? s.split("|") : s.split(/\s+/);
    parts = parts.map(p => p.trim()).filter(Boolean);

    const out = [];
    for (let i = 0; i < beats; i++) out.push(parts[i] ?? ".");
    return out;
  }

  //  Correct escaping for preview/export rendering
  function escapeHtml(s){
    return String(s ?? "")
      .replaceAll("&","&amp;")
      .replaceAll("<","&lt;")
      .replaceAll(">","&gt;")
      .replaceAll('"',"&quot;")
      .replaceAll("'","&#039;");
  }

  // ---------- DOM ----------
  const el = (id) => document.getElementById(id);
  const sectionsEl = el("sections");
  const htmlPreviewEl = el("htmlPreview");


  // Meta bindings
  ["title","composer","key","timeSig","tempo"].forEach(k => {
    el(k).addEventListener("input", () => {
      state.meta[k] = el(k).value;
      renderAll();
    });
  });

  el("notation").addEventListener("change", () => { state.meta.notation = el("notation").value; renderAll(); });

  el("barsPerLine").addEventListener("change", () => {
    state.meta.barsPerLine = parseInt(el("barsPerLine").value, 10);
    state.sections.forEach(ensureLines);
    renderSections();
    renderAll();
  });

  el("cellWidth").addEventListener("change", () => {
    state.meta.cellWidth = parseInt(el("cellWidth").value, 10) || 10;
    renderAll();
  });

  el("beatGuidePx").addEventListener("change", () => {
    state.meta.beatGuidePx = parseInt(el("beatGuidePx").value, 10) || 64;
    document.documentElement.style.setProperty("--beatGuidePx", state.meta.beatGuidePx + "px");
  });

  el("beatsOverride").addEventListener("change", () => {
    state.meta.beatsOverride = el("beatsOverride").value;
    renderAll();
  });

  el("notes").addEventListener("input", () => { state.notes = el("notes").value; renderAll(); });

  // Buttons
 el("addSectionBtn").addEventListener("click", () => {
  const s = newSection("Verse", 8);
  ensureLines(s);
  state.sections.push(s);

  renderSections();
  renderAll();

  //  Smooth scroll to the newly added section
  setTimeout(() => {
    const allSections = document.querySelectorAll(".section");
    const last = allSections[allSections.length - 1];
    if (last) {
      last.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  }, 50);
});

el("addSectionBottomBtn").addEventListener("click", () => {
  el("addSectionBtn").click();
});

  el("clearBtn").addEventListener("click", () => {
    state.sections = [];
    state.notes = "";
    el("notes").value = "";
    renderSections();
    renderAll();
//    htmlExportEl.textContent = "";
  });

    el("printBtn").addEventListener("click", () => {
  	window.print();
  });
    
  // JSON
  el("downloadJsonBtn").addEventListener("click", () => {
    const blob = new Blob([JSON.stringify(state, null, 2)], {type:"application/json"});
    const a = document.createElement("a");
    a.href = URL.createObjectURL(blob);
    a.download = (state.meta.title?.trim() ? slugify(state.meta.title) : "nashville_chart") + ".json";
    a.click();
    URL.revokeObjectURL(a.href);
  });

  el("loadJsonInput").addEventListener("change", async (ev) => {
    const file = ev.target.files?.[0];
    if (!file) return;
    const text = await file.text();
    try { hydrate(JSON.parse(text)); toast("Loaded JSON."); }
    catch(e) { toast("Invalid JSON file."); }
    ev.target.value = "";
  });

  // ---------- Rendering ----------
  function renderSections() {
    sectionsEl.innerHTML = "";
    if (state.sections.length === 0) {
      const empty = document.createElement("div");
      empty.className = "muted";
      empty.textContent = "No sections yet. Click “Add Section” to start.";
      sectionsEl.appendChild(empty);
      htmlPreviewEl.innerHTML = `<div class="muted">No chart sections yet.</div>`;
      return;
    }

    const cols = parseInt(state.meta.barsPerLine, 10) || 4;

    state.sections.forEach((section, sidx) => {
      ensureLines(section);

      const wrap = document.createElement("div");
      wrap.className = "section";

      const top = document.createElement("div");
      top.className = "section-top";

      const left = document.createElement("div");
      left.style.display = "flex";
      left.style.flexWrap = "wrap";
      left.style.gap = "10px";
      left.style.alignItems = "center";

      const nameSel = document.createElement("select");
      defaultSectionTypes.forEach(t => {
        const opt = document.createElement("option");
        opt.value = t; opt.textContent = t;
        if (t === section.name) opt.selected = true;
        nameSel.appendChild(opt);
      });
      const customOpt = document.createElement("option");
      customOpt.value = "__custom";
      customOpt.textContent = "Custom…";
      nameSel.appendChild(customOpt);

      nameSel.addEventListener("change", () => {
        if (nameSel.value === "__custom") {
          const custom = prompt("Section name:", section.name) || section.name;
          section.name = custom;
        } else section.name = nameSel.value;
        renderSections();
        renderAll();
      });

      const barsInput = document.createElement("input");
      barsInput.type = "number";
      barsInput.min = "1";
      barsInput.step = "1";
      barsInput.value = section.bars;
      barsInput.style.maxWidth = "110px";
      barsInput.title = "Number of bars in this section";
      barsInput.addEventListener("input", () => {
        section.bars = parseInt(barsInput.value || "1", 10);
        ensureLines(section);
        renderSections();
        renderAll();
      });

      const label = document.createElement("strong");
      label.textContent = `${section.name}`;

      const pill = document.createElement("span");
      pill.className = "pill";
      pill.textContent = `${section.bars} bars`;

      left.appendChild(label);
      left.appendChild(pill);
      left.appendChild(document.createElement("span")).outerHTML = '<span class="muted">Name</span>';
      left.appendChild(nameSel);
      left.appendChild(document.createElement("span")).outerHTML = '<span class="muted">Bars</span>';
      left.appendChild(barsInput);

      const right = document.createElement("div");
      right.className = "btns";

      const addLineBtn = document.createElement("button");
      addLineBtn.type = "button";
      addLineBtn.textContent = "+ Add line";
      addLineBtn.addEventListener("click", () => {
        section.bars = (parseInt(section.bars, 10) || 0) + cols;
        ensureLines(section);
        renderSections();
        renderAll();
      });

      const delBtn = document.createElement("button");
      delBtn.type = "button";
      delBtn.className = "danger";
      delBtn.textContent = "Remove section";
      delBtn.addEventListener("click", () => {
        state.sections.splice(sidx, 1);
        renderSections();
        renderAll();
      });

const dupBtn = document.createElement("button");
dupBtn.type = "button";
dupBtn.textContent = "Duplicate section";
dupBtn.addEventListener("click", () => {

  //  Deep clone section (including all lines/measures)
  const clone = JSON.parse(JSON.stringify(section));

  //  Regenerate IDs so DOM + state stay stable
  clone.id = crypto.randomUUID();
  clone.name = clone.name + " (copy)";

  clone.lines.forEach(line => {
    line.id = crypto.randomUUID();
    line.measures.forEach(me => {
      me.id = crypto.randomUUID();
    });
  });

  //  Insert directly below current section
  state.sections.splice(sidx + 1, 0, clone);

  renderSections();
  renderAll();

  //  Scroll to duplicated section
  setTimeout(() => {
    const allSections = document.querySelectorAll(".section");
    const target = allSections[sidx + 1];
    if (target) {
      target.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  }, 50);
});
      
    const addBelowBtn = document.createElement("button");
		addBelowBtn.type = "button";
		addBelowBtn.textContent = "+ Add section below";

		addBelowBtn.addEventListener("click", () => {
  	const newSec = newSection("Verse", 8);
  		ensureLines(newSec);

  	//  Insert directly after current section
  	state.sections.splice(sidx + 1, 0, newSec);

  	renderSections();
  	renderAll();

  //  Scroll to the newly inserted section
  setTimeout(() => {
    const allSections = document.querySelectorAll(".section");
    const target = allSections[sidx + 1];
    if (target) {
      target.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  }, 50);
});    
         
right.appendChild(addLineBtn);
right.appendChild(delBtn);
right.appendChild(addBelowBtn);
right.appendChild(dupBtn);
 
      top.appendChild(left);
      top.appendChild(right);

      const lines = document.createElement("div");
      lines.className = "lines";

      section.lines.forEach((line, lidx) => {
        const lineEl = document.createElement("div");
        lineEl.className = "line";

        const meta = document.createElement("div");
        meta.className = "meta";

        const lineLabel = document.createElement("input");
        lineLabel.value = line.label;
        lineLabel.addEventListener("input", () => { line.label = lineLabel.value; renderAll(); });

        const small = document.createElement("div");
        small.className = "muted";
        small.textContent = `Measures: ${line.measures.length}`;

        meta.appendChild((() => { const l=document.createElement("label"); l.textContent="Line label"; return l; })());
        meta.appendChild(lineLabel);
        meta.appendChild(small);

        const measuresWrap = document.createElement("div");
        measuresWrap.className = "measures";

        const grid = document.createElement("div");
        grid.className = "measures-grid";
        grid.style.setProperty("--cols", cols);

        line.measures.forEach((m, midx) => {
          const mEl = document.createElement("div");
          mEl.className = "measure";

          const mLab = document.createElement("div");
          mLab.className = "muted";
          mLab.textContent = `Bar ${lidx*cols + midx + 1}`;

          const ta = document.createElement("textarea");
          const beats = effectiveBeatsPerBar();
          ta.placeholder = beats
            ? `One token per beat (${beats}): e.g., ` + (beats === 4 ? "1 . 4 ." : "1 . 4 . 5 .")
            : "One token per beat: e.g., 1 . 4 .";
          ta.value = m.text;

          ta.addEventListener("input", () => { m.text = ta.value; renderAll(); });

          mEl.appendChild(mLab);
          mEl.appendChild(ta);
          grid.appendChild(mEl);
        });

        measuresWrap.appendChild(grid);
        lineEl.appendChild(meta);
        lineEl.appendChild(measuresWrap);
        lines.appendChild(lineEl);
      });

      wrap.appendChild(top);
      wrap.appendChild(lines);
      sectionsEl.appendChild(wrap);
    });
  }

  // Build a preview body (chart only) as HTML
  function buildHtmlBody() {
    const cols = parseInt(state.meta.barsPerLine, 10) || 4;
    const beats = effectiveBeatsPerBar();
    const cellWidth = parseInt(state.meta.cellWidth, 10) || 10;

    // Drive CSS vars for preview sizing
    document.documentElement.style.setProperty("--cols", cols);
    document.documentElement.style.setProperty("--beats", beats);
    document.documentElement.style.setProperty("--cellCh", cellWidth + "ch");

    let out = "";

    if (state.sections.length === 0) {
      out += `<div class="muted">No chart sections yet.</div>`;
      return out;
    }

    state.sections.forEach(sec => {
      ensureLines(sec);
      out += `<div class="p-sectionTitle">${escapeHtml(sec.name)} (${escapeHtml(sec.bars)} bars)</div>`;

      sec.lines.forEach(line => {
        out += `<div class="p-line">`;
        line.measures.forEach(mm => {
          const tokens = splitIntoBeatTokens(mm.text, beats);
          out += `<div class="p-bar">`;
 
            tokens.forEach(tok => {
  			const t = (tok ?? ".").trim() || ".";

  			let sizeClass = "";
  			if (t.length >= 6) sizeClass = "xlong";
  			else if (t.length >= 4) sizeClass = "long";

 			const cls = t === "."
    		? "p-beat p-dot"
    		: `p-beat ${sizeClass}`;                                   
            out += `<div class="${cls}">${escapeHtml(t === "." ? "·" : t)}</div>`;
          });
          out += `</div>`;
        });
        out += `</div>`;
      });
    });

    if (String(state.notes || "").trim()) {
      out += `<div class="p-notesTitle">Notes / Cues</div>`;
      out += `<div class="p-notes">${escapeHtml(state.notes.trim())}</div>`;
    }
    return out;
  }

  function renderHtmlPreview() {
    const m = state.meta;
    const metaParts = [];
    if (m.title?.trim()) metaParts.push(`<strong>${escapeHtml(m.title.trim())}</strong>`);
    if (m.composer?.trim()) metaParts.push(`Composer: <strong>${escapeHtml(m.composer.trim())}</strong>`);
    if (m.key?.trim()) metaParts.push(`Key: <strong>${escapeHtml(m.key.trim())}</strong>`);
    if (m.timeSig?.trim()) metaParts.push(`Time: <strong>${escapeHtml(m.timeSig.trim())}</strong>`);
    if (m.tempo?.trim()) metaParts.push(`Tempo: <strong>${escapeHtml(m.tempo.trim())}${m.tempo ? " BPM" : ""}</strong>`);
 //   if (m.notation) metaParts.push(`Notation: <strong>${escapeHtml(m.notation)}</strong>`);


  const meta = `
  <div class="p-meta">
    <div class="p-title">${metaParts[0] || ""}</div>
    <div class="p-meta-row">
      ${metaParts.slice(1).map(p => `<div class="p-meta-item">${p}</div>`).join("")}
    </div>
  </div>
`; 
    htmlPreviewEl.innerHTML = meta + buildHtmlBody();
  }
    
  function renderAll() {
    document.documentElement.style.setProperty("--beatGuidePx", (state.meta.beatGuidePx || 64) + "px");
    renderHtmlPreview();
  }

  function hydrate(obj) {
    state.meta = Object.assign(state.meta, obj.meta || {});
    state.notes = obj.notes || "";
    state.sections = Array.isArray(obj.sections) ? obj.sections : [];

    state.sections.forEach(sec => {
      sec.id ||= crypto.randomUUID();
      sec.lines ||= [];
      sec.lines.forEach(line => {
        line.id ||= crypto.randomUUID();
        line.measures ||= [];
        line.measures.forEach(me => me.id ||= crypto.randomUUID());
      });
      ensureLines(sec);
    });

    el("title").value = state.meta.title || "";
    el("composer").value = state.meta.composer || "";
    el("key").value = state.meta.key || "";
    el("timeSig").value = state.meta.timeSig || "";
    el("tempo").value = state.meta.tempo || "";
    el("notation").value = state.meta.notation || "numbers";
    el("barsPerLine").value = String(state.meta.barsPerLine || 4);
    el("cellWidth").value = String(state.meta.cellWidth || 10);
    el("beatGuidePx").value = String(state.meta.beatGuidePx || 64);
    el("beatsOverride").value = String(state.meta.beatsOverride || "");
    el("notes").value = state.notes || "";

    document.documentElement.style.setProperty("--beatGuidePx", (state.meta.beatGuidePx || 64) + "px");

    renderSections();
    renderAll();
  }

  function slugify(s){
    return (s || "chart").toLowerCase().trim()
      .replace(/['"]/g,"")
      .replace(/[^a-z0-9]+/g,"-")
      .replace(/(^-|-$)/g,"");
  }

  function toast(msg){
    const t = document.createElement("div");
    t.textContent = msg;
    t.style.position="fixed";
    t.style.bottom="18px";
    t.style.left="50%";
    t.style.transform="translateX(-50%)";
    t.style.background="rgba(16,26,49,.95)";
    t.style.border="1px solid rgba(147,164,199,.35)";
    t.style.padding="10px 12px";
    t.style.borderRadius="999px";
    t.style.boxShadow="0 10px 30px rgba(0,0,0,.35)";
    t.style.zIndex="9999";
    document.body.appendChild(t);
    setTimeout(()=>{ t.style.opacity="0"; t.style.transition="opacity .25s"; }, 1200);
    setTimeout(()=>{ t.remove(); }, 1600);
  }

  // Initial render
  document.documentElement.style.setProperty("--beatGuidePx", state.meta.beatGuidePx + "px");
  renderSections();
  renderAll();
</script>
</body>
</html>
