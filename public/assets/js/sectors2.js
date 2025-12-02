async function loadSectors2() {
    const sortBySelect = document.getElementById('sortBy');
    const sortBy = sortBySelect ? sortBySelect.value : 'market_cap_change_24h';

    try {
        const res = await fetch(`/api/sectors2?sort_by=${encodeURIComponent(sortBy)}`);
        if (!res.ok) throw new Error('HTTP error ' + res.status);
        const data = await res.json();

        const tbody = document.querySelector("#sector2Table tbody");
        tbody.innerHTML = "";

        // fill rows
        data.forEach((sector, idx) => {
            let logos = [];
            try { logos = JSON.parse(sector.top_3_logos || "[]"); } catch(e) { logos = []; }

            const tr = document.createElement('tr');
            tr.className = "hover:bg-gray-50";

            tr.innerHTML = `
                <td class="p-2 align-middle border-b whitespace-nowrap">${idx + 1}</td>
                <td class="p-2 align-middle border-b whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="mr-3">
                        ${ logos.length ? `<img src="${logos[0]}" class="rounded-full w-8 h-8" alt="logo">` : `<div class="w-8 h-8 bg-gray-200 rounded-full"></div>` }
                      </div>
                      <div>${sector.name}</div>
                    </div>
                </td>
                <td class="p-2 text-center border-b whitespace-nowrap">$${Number(sector.market_cap || 0).toLocaleString()}</td>
                <td class="p-2 text-center border-b whitespace-nowrap">
                  <span class="${(sector.market_cap_change_24h||0) >= 0 ? 'text-green-600' : 'text-red-600'}">
                    ${Number(sector.market_cap_change_24h || 0).toFixed(2)}%
                  </span>
                </td>
                <td class="p-2 text-center border-b whitespace-nowrap">$${Number(sector.volume_24h || 0).toLocaleString()}</td>
                <td class="p-2 text-center border-b whitespace-nowrap">
                  ${ logos.map(u => `<img src="${u}" class="inline-block w-7 h-7 rounded-full mr-1">`).join('') }
                </td>
            `;
            tbody.appendChild(tr);
        });

        // updated at: gunakan updated_at_api dari first item (atau now)
        const last = data.length ? (data[0].updated_at_api || null) : null;
        const lastEl = document.getElementById('lastUpdated');
        if (lastEl) lastEl.textContent = last ? (new Date(last)).toLocaleString() : '-';

    } catch (err) {
        console.error("Gagal load sectors2:", err);
    }
}

// initial load dan auto refresh tiap 60 detik
document.addEventListener('DOMContentLoaded', function() {
    loadSectors2();
    setInterval(loadSectors2, 60000);
});
