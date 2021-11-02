//From: https://www.npmjs.com/package/string-similarity
!function (t, e) {
  "object" == typeof exports && "object" == typeof module ? module.exports = e() : "function" == typeof define && define.amd ? define([], e) : "object" == typeof exports ? exports.stringSimilarity = e() : t.stringSimilarity = e()
}(self, (function () {
  return t = {
    138: t => {
      function e(t, e) {
        if ((t = t.replace(/\s+/g, "")) === (e = e.replace(/\s+/g, ""))) return 1;
        if (t.length < 2 || e.length < 2) return 0;
        let r = new Map;
        for (let e = 0; e < t.length - 1; e++) {
          const n = t.substring(e, e + 2), o = r.has(n) ? r.get(n) + 1 : 1;
          r.set(n, o)
        }
        let n = 0;
        for (let t = 0; t < e.length - 1; t++) {
          const o = e.substring(t, t + 2), s = r.has(o) ? r.get(o) : 0;
          s > 0 && (r.set(o, s - 1), n++)
        }
        return 2 * n / (t.length + e.length - 2)
      }

      t.exports = {
        compareTwoStrings: e, findBestMatch: function (t, r) {
          if (!function (t, e) {
            return "string" == typeof t && !!Array.isArray(e) && !!e.length && !e.find((function (t) {
              return "string" != typeof t
            }))
          }(t, r)) throw new Error("Bad arguments: First argument should be a string, second should be an array of strings");
          const n = [];
          let o = 0;
          for (let s = 0; s < r.length; s++) {
            const i = r[s], f = e(t, i);
            n.push({target: i, rating: f}), f > n[o].rating && (o = s)
          }
          return {ratings: n, bestMatch: n[o], bestMatchIndex: o}
        }
      }
    }
  }, e = {}, function r(n) {
    if (e[n]) return e[n].exports;
    var o = e[n] = {exports: {}};
    return t[n](o, o.exports, r), o.exports
  }(138);
  var t, e
}));

var soundex = function (s) {
  var a = s.toLowerCase().split(''),
    f = a.shift(),
    r = '',
    codes = {
      a: '', e: '', i: '', o: '', u: '',
      b: 1, f: 1, p: 1, v: 1,
      c: 2, g: 2, j: 2, k: 2, q: 2, s: 2, x: 2, z: 2,
      d: 3, t: 3,
      l: 4,
      m: 5, n: 5,
      r: 6
    };

  r = f +
    a
      .map(function (v, i, a) { return codes[v] })
      .filter(function (v, i, a) {
        return ((i === 0) ? v !== codes[f] : v !== a[i - 1]);
      })
      .join('');

  return (r + '000').slice(0, 4).toUpperCase();
};

/*
German codes
soundex.codes = {
  a: '', e: '', i: '', o: '', u: '',
  b: 1, f: 1, p: 1, v: 1,
  c: 2, g: 2, k: 2, q: 2, s: 2, x: 2, z: 2,
  d: 3, t: 3,
  l: 4,
  m: 5, n: 5,
  r: 6,
  ch: 7
}
*/
