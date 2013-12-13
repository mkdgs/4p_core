$4p.levenshtein = function(a, b) {
	if (a == b)
	    return 0;
	var a_len = a.length, b_len = b.length;
	if (a_len === 0)
	    return b_len;
	if (b_len === 0)
	    return a_len;

	var c = new Array(a_len + 1);
	var d = new Array(a_len + 1);
	var s = false, a_idx = 0, b_idx = 0, cost = 0, char_a = '', char_b = '', m_min, g, h, v_tmp;

	try {
	    s = !('0')[0];
	} catch (e) {
	    split = true;
	}
	if (s) {
	    a = a.split('');
	    b = b.split('');
	}
	for (a_idx = 0; a_idx < a_len + 1; a_idx++)
	    c[a_idx] = a_idx;
	for (b_idx = 1; b_idx <= b_len; b_idx++) {
	    d[0] = b_idx;
	    char_b = b[b_idx - 1];
	    for (a_idx = 0; a_idx < a_len; a_idx++) {
		char_a = a[a_idx];
		cost = (char_a == char_b) ? 0 : 1;
		m_min = c[a_idx + 1] + 1;
		g = d[a_idx] + 1;
		h = c[a_idx] + cost;
		if (g < m_min)
		    m_min = g;
		if (h < m_min)
		    m_min = h;
		d[a_idx + 1] = m_min;
	    }
	    v_tmp = c;
	    c = d;
	    d = v_tmp;
	}
	return c[a_len];
 };