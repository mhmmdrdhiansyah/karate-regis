@props(['prefix' => 'mc'])

<style>
    .{{ $prefix }}-card { background: #fff; border: 1px dashed #e4e6ef; border-radius: 8px; margin-bottom: 10px; overflow: hidden; }
    .{{ $prefix }}-card-hd { padding: 12px 14px; display: flex; align-items: center; gap: 10px; cursor: pointer; -webkit-tap-highlight-color: transparent; user-select: none; }
    .{{ $prefix }}-card-hd:active { background: #f9fafb; }
    .{{ $prefix }}-card-av { width: 42px; height: 42px; border-radius: 50%; overflow: hidden; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: .95rem; }
    .{{ $prefix }}-card-nm { flex: 1; min-width: 0; font-weight: 700; font-size: .88rem; color: #3f4254; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .{{ $prefix }}-card-em { font-size: .72rem; color: #b5b5c3; font-weight: 600; margin-top: 2px; }
    .{{ $prefix }}-card-bg { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 2px; }
    .{{ $prefix }}-card-bg .badge { font-size: .6rem; padding: 2px 6px; border-radius: 4px; }
    .{{ $prefix }}-card-arr { flex-shrink: 0; color: #b5b5c3; transition: transform .25s ease; font-size: .7rem; }
    .{{ $prefix }}-card.open .{{ $prefix }}-card-arr { transform: rotate(180deg); }
    .{{ $prefix }}-card-bd { max-height: 0; overflow: hidden; transition: max-height .3s cubic-bezier(.4, 0, .2, 1); }
    .{{ $prefix }}-card.open .{{ $prefix }}-card-bd { max-height: 500px; }
    .{{ $prefix }}-card-dt { padding: 0 14px 12px; }
    .{{ $prefix }}-card-row { display: flex; justify-content: space-between; align-items: center; padding: 7px 0; border-bottom: 1px solid #f3f6f9; }
    .{{ $prefix }}-card-row:last-child { border-bottom: none; }
    .{{ $prefix }}-card-lbl { font-size: .72rem; color: #b5b5c3; font-weight: 600; }
    .{{ $prefix }}-card-val { font-size: .78rem; color: #3f4254; font-weight: 600; text-align: right; max-width: 60%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .{{ $prefix }}-card-acts { display: flex; gap: 6px; padding: 6px 14px 12px; border-top: 1px dashed #e4e6ef; }
    .{{ $prefix }}-card-acts .btn { flex: 1; font-size: .72rem; padding: 6px 0; border-radius: 6px; display: flex; align-items: center; justify-content: center; gap: 4px; }
</style>
