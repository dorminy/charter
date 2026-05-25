# charter
A web-based Nashville number system chord chart creator 
Copyright (C)2026 Mark Dorminy
 
- Setup fields: title, composer, key, time signature, tempo
- Aligned beats: fixed-width beat cells in HTML preview/export (monospace)
- Supports letters (chord names) or numbers
- Save/Load JSON, print chart
- Requirements: PHP 7.4+ (no DB needed)

  Simple editing and notation: Enter one token per beat in each bar (separate with spaces).
  Examples (4/4): 1 . 4 . ->  "1" chord on beat 1, "4" chord on beat 3
  Push: ^4
  Hold: 1~ (diamond in NNS notation)
  Choke: 1! (dorito in NNS notation)
  Passing: (6m 5)
