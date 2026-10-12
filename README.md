# charter
A browser-based chart editor designed for session musicians, arrangers, worship teams, songwriters, and bandleaders.

Nashville Chart Builder creates clean, printable chord charts using either Nashville Numbers or standard chord notation, supports lyric sheets, validates rhythmic bar durations, and saves projects as portable JSON files.

No installation required. Everything runs locally in a single HTML file.

---

## Features

### Chord Chart Creation

- Create structured song charts with:
  - Intro
  - Verse
  - Chorus
  - Bridge
  - Solo
  - Outro
  - Custom sections
- Editable section names and section letters
- Adjustable number of bars per section
- Reorder, duplicate, add, and remove sections

### Nashville Numbers and Chord Notation

Supports both:

- Nashville Number System (NNS)
- Standard chord symbols

Examples:

```
1   4   5   1
```

```
C   F   G   C
```

Built-in conversion tools:

- Chords → NNS
- NNS → Chords

Automatic notation detection determines whether the chart is currently using chord symbols or Nashville numbers.

---

## Beat Duration Support

Unlike many chart editors, Nashville Chart Builder allows explicit beat durations within a measure.

Examples:

| Input | Meaning |
|---------|---------|
| C | Full measure |
| C· | 1 beat |
| C·· | 2 beats |
| C··· | 3 beats |
| C··· D· | 3 + 1 |
| C·· D·· | 2 + 2 |
| C D | Equal split |

Supports:

- 4/4
- 3/4
- 6/8
- 12/8
- 5/8
- Other custom meters

Built-in validation warns when durations exceed or fail to fill a measure.

---

## Advanced Chart Symbols

### Repeat Markers

```
|: 1 4 5 1 :|
```

Supports:

- Repeat start (`|:`)
- Repeat end (`:|`)

### Holds

Use:

```
◇
```

to indicate a sustained chord.

### Chokes

Use:

```
▽
```

to indicate a cut-off/staccato ending.

### Ties Across Bars

Use:

```
—
```

to extend a held chord across additional measures.

Example:

```
1◇
—
—
```

Sustains the same chord across three bars.

---

## Lyric Sheet Mode

A separate lyric sheet can be generated alongside the chord chart.

Features:

- Independent lyric sections
- One multi-line lyric block per section
- Instrumental sections
- "No Lyrics" toggle
- Separate print layout
- Lyrics and chart saved together in one JSON file

The lyric sheet mirrors the chart structure initially but can be edited independently afterward.

---

## Chord Pad

Built-in chord suggestion pad generates diatonic chords for the selected key.

Examples:

Key of C:

```
C
Dm
Em
F
G
Am
B°
```

Click any chord to insert it into the focused measure.

A recent-chords panel provides fast access to frequently used chords.

---

## Printing

Optimized printable layouts:

### Chord Charts

- Automatic font scaling
- Single-page fitting
- Multi-column fallback
- Clean session-chart formatting
- Optional section letters
- Optional bar numbers

### Lyric Sheets

- Automatic scaling
- Single or two-column layouts
- Print-friendly formatting

Charts are optimized for US Letter paper.

---

## Saving and Loading

Charts can be saved as JSON files.

Included data:

- Song metadata
- Sections
- Chords
- Notes
- Lyrics
- Chart settings

Load previously saved charts directly into the editor.

No server required.

---

## Song Metadata

Supported metadata fields:

- Title
- Composer
- Key
- Time Signature
- Tempo (BPM)
- Bars Per Line

---

## Keyboard Navigation

| Key | Action |
|-------|---------|
| Enter | Next bar |
| Tab | Next bar |
| Shift+Tab | Previous bar |
| ← | Previous bar |
| → | Next bar |
| ↑ | Previous row |
| ↓ | Next row |

---

## Help System

A built-in help dialog explains:

- Nashville Number notation
- Beat-duration syntax
- Repeats
- Holds
- Ties
- Printing
- Lyric sheets
- Conversion tools

Open the help window by:

- Clicking **Help**
- Pressing **?**

---

## Project Structure

This project is intentionally implemented as a single-file application.

```
index.html
```

Contains:

- HTML UI
- CSS styling
- Application logic
- Printing engine
- JSON import/export
- NNS conversion engine
- Lyric sheet system

No build process or framework dependencies are required.

---

## Browser Compatibility

Tested against modern browsers:

- Google Chrome
- Microsoft Edge
- Mozilla Firefox

Requires JavaScript enabled.

---

## Design Goals

- Fast chart creation during rehearsals and sessions
- Nashville Number System workflow
- Clean printable charts
- No account required
- No cloud dependency
- Portable JSON-based storage
- Mobile, tablet, and desktop usability

---

## License
- GPL v3

---

## Author

**Mark Dorminy**

Nashville Chart Builder was developed as a lightweight tool for creating professional-quality Nashville-style charts and companion lyric sheets directly in the browser.
