<script>
    // DA Green Analytics — centralized chart color system.
    // Change colors here once; every chart on the dashboard reuses them.
    const DA_COLORS = {
        primaryGreen: '#16803C',
        darkGreen:    '#0F6B32',
        mediumGreen:  '#3FAE68',
        lightGreen:   '#8ACD9F',
        slate:        '#64748B',
        darkText:     '#1E293B',
        grid:         '#E2E8F0',
        warning:      '#D99A00',
        problem:      '#C94B4B',
        blue:         '#3B82A0',
    };

    // Ranking scale: darkest (highest value) -> lightest (lowest value).
    const DA_GREEN_SCALE = ['#0F6B32', '#16803C', '#3FAE68', '#4FAF70', '#6BBF87', '#8ACD9F'];

    // For "problem" charts — muted red down to amber, not a rainbow.
    const DA_PROBLEM_SCALE = ['#C94B4B', '#D97706', '#D99A00'];

    // Blue shades — used to visually separate "reasons/demographic" charts
    // from green "ranking" charts so two different donuts don't look identical.
    const DA_BLUE_SCALE = ['#1E4E5F', '#2E6B85', '#3B82A0', '#63A4BE', '#8FC2D6', '#B8DCE8'];

    // Pick maximally spread-out shades from a scale array. For 2 categories
    // this returns the two most contrasting ends of the scale (e.g. darkest
    // + lightest) instead of two adjacent, hard-to-distinguish shades.
    function daSpreadShades(scale, n) {
        if (n <= 1) return [scale[0]];
        const result = [];
        for (let i = 0; i < n; i++) {
            const idx = Math.round((i * (scale.length - 1)) / (n - 1));
            result.push(scale[idx]);
        }
        return result;
    }

    function daGreenShades(n) {
        return daSpreadShades(DA_GREEN_SCALE, n);
    }

    function daBlueShades(n) {
        return daSpreadShades(DA_BLUE_SCALE, n);
    }

    function daProblemShades(n) {
        return daSpreadShades(DA_PROBLEM_SCALE, n);
    }

    if (window.Chart) {
        Chart.defaults.color = DA_COLORS.darkText;
        Chart.defaults.borderColor = DA_COLORS.grid;
        Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    }
</script>