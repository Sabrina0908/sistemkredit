const allowedOptions = {
    jenis_nasabah: ["Rekomendasi", "Baru", "Bermasalah", "Blacklist"],
    jaminan: ["Sertifikat", "BPKB Mobil", "BPKB Motor", "Tanah"],
    pekerjaan: ["Karyawan", "Karyawan Kontrak", "Usaha Sendiri", "Usaha Keluarga", "Usaha Kecil"],
};

const form = document.getElementById("creditForm");
const errorBox = document.getElementById("errorBox");
const resultSection = document.getElementById("resultSection");
const resultBox = document.getElementById("resultBox");
const resultTitle = document.getElementById("resultTitle");
const resultRule = document.getElementById("resultRule");
const summaryList = document.getElementById("summaryList");
const scoreList = document.getElementById("scoreList");
const resetButton = document.getElementById("resetButton");

function formatRupiah(value) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        maximumFractionDigits: 0,
    }).format(value);
}

function getBobotJenisNasabah(jenis) {
    const normalized = jenis.toLowerCase();

    if (normalized === "rekomendasi") {
        return 100;
    }
    if (normalized === "baru") {
        return 70;
    }
    if (normalized === "bermasalah") {
        return 50;
    }
    if (normalized === "blacklist") {
        return 10;
    }

    return 0;
}

function getBobotPenghasilan(gaji) {
    if (gaji > 3000000) {
        return 100;
    }
    if (gaji === 3000000) {
        return 60;
    }
    if (gaji >= 1000000) {
        return 40;
    }

    return 20;
}

function getBobotJaminan(jaminan) {
    const normalized = jaminan.toLowerCase();

    if (normalized.includes("sertifikat") || normalized.includes("tanah")) {
        return 100;
    }
    if (normalized.includes("mobil")) {
        return 60;
    }
    if (normalized.includes("motor")) {
        return 50;
    }

    return 0;
}

function getBobotTanggungan(tanggungan) {
    if (tanggungan === 1) {
        return 100;
    }
    if (tanggungan === 2) {
        return 60;
    }
    if (tanggungan === 3) {
        return 50;
    }

    return 20;
}

function getBobotPekerjaan(pekerjaan) {
    const normalized = pekerjaan.toLowerCase();

    if (normalized.includes("sendiri")) {
        return 100;
    }
    if (normalized.includes("keluarga") || normalized.includes("join")) {
        return 70;
    }
    if (normalized.includes("karyawan")) {
        return 20;
    }

    return 0;
}

function inferKelayakan(pengajuan, skor) {
    if (pengajuan <= 3000000) {
        if (skor > 28) {
            return ["LAYAK", 1];
        }
        if (skor === 28) {
            return ["DIPERTIMBANGKAN", 2];
        }
        return ["BELUM LAYAK", 3];
    }

    if (pengajuan <= 7000000) {
        if (skor > 58) {
            return ["LAYAK", 4];
        }
        if (skor === 58) {
            return ["DIPERTIMBANGKAN", 5];
        }
        return ["BELUM LAYAK", 6];
    }

    if (pengajuan <= 15000000) {
        if (skor > 78) {
            return ["LAYAK", 7];
        }
        if (skor === 78) {
            return ["DIPERTIMBANGKAN", 8];
        }
        return ["BELUM LAYAK", 9];
    }

    if (skor > 92) {
        return ["LAYAK", 10];
    }
    if (skor === 92) {
        return ["DIPERTIMBANGKAN", 11];
    }
    return ["BELUM LAYAK", 12];
}

function renderDefinitionList(target, items) {
    target.innerHTML = items
        .map(
            ([label, value]) => `
                <div>
                    <dt>${label}</dt>
                    <dd>${value}</dd>
                </div>
            `
        )
        .join("");
}

function showError(message) {
    errorBox.textContent = message;
    errorBox.classList.remove("hidden");
}

function hideError() {
    errorBox.textContent = "";
    errorBox.classList.add("hidden");
}

function getStatusClass(keputusan) {
    if (keputusan === "LAYAK") {
        return "status-layak";
    }
    if (keputusan === "DIPERTIMBANGKAN") {
        return "status-warning";
    }
    return "status-tidak";
}

function getFormData() {
    return {
        nama: form.nama.value.trim(),
        jenis_nasabah: form.jenis_nasabah.value.trim(),
        penghasilan: Number.parseInt(form.penghasilan.value, 10),
        jaminan: form.jaminan.value.trim(),
        tanggungan: Number.parseInt(form.tanggungan.value, 10),
        pekerjaan: form.pekerjaan.value.trim(),
        pengajuan: Number.parseInt(form.pengajuan.value, 10),
    };
}

function isValid(data) {
    return (
        data.nama !== "" &&
        allowedOptions.jenis_nasabah.includes(data.jenis_nasabah) &&
        Number.isInteger(data.penghasilan) &&
        data.penghasilan >= 0 &&
        allowedOptions.jaminan.includes(data.jaminan) &&
        Number.isInteger(data.tanggungan) &&
        data.tanggungan >= 0 &&
        allowedOptions.pekerjaan.includes(data.pekerjaan) &&
        Number.isInteger(data.pengajuan) &&
        data.pengajuan > 0
    );
}

form.addEventListener("submit", (event) => {
    event.preventDefault();
    hideError();

    const data = getFormData();

    if (!isValid(data)) {
        resultSection.classList.add("hidden");
        showError("Data tidak valid. Pastikan semua field sudah diisi dengan benar.");
        return;
    }

    const bobotJenis = getBobotJenisNasabah(data.jenis_nasabah);
    const bobotPenghasilan = getBobotPenghasilan(data.penghasilan);
    const bobotJaminan = getBobotJaminan(data.jaminan);
    const bobotTanggungan = getBobotTanggungan(data.tanggungan);
    const bobotPekerjaan = getBobotPekerjaan(data.pekerjaan);
    const skorTotal = bobotJenis + bobotPenghasilan + bobotJaminan + bobotTanggungan + bobotPekerjaan;
    const skorRataRata = skorTotal / 5;
    const [keputusan, rule] = inferKelayakan(data.pengajuan, skorRataRata);

    resultBox.className = `result-box ${getStatusClass(keputusan)}`;
    resultTitle.textContent = keputusan;
    resultRule.textContent = `Rule ${rule} digunakan untuk pengajuan ${formatRupiah(data.pengajuan)}.`;

    renderDefinitionList(summaryList, [
        ["Nama Nasabah", data.nama],
        ["Jenis Nasabah", data.jenis_nasabah],
        ["Penghasilan", formatRupiah(data.penghasilan)],
        ["Jaminan", data.jaminan],
        ["Jumlah Tanggungan", String(data.tanggungan)],
        ["Pekerjaan", data.pekerjaan],
        ["Jumlah Pengajuan", formatRupiah(data.pengajuan)],
        ["Skor Rata-rata Bobot", skorRataRata.toFixed(2).replace(".", ",")],
    ]);

    renderDefinitionList(scoreList, [
        ["Bobot Jenis Nasabah", String(bobotJenis)],
        ["Bobot Penghasilan", String(bobotPenghasilan)],
        ["Bobot Jaminan", String(bobotJaminan)],
        ["Bobot Tanggungan", String(bobotTanggungan)],
        ["Bobot Pekerjaan", String(bobotPekerjaan)],
        ["Skor Total", String(skorTotal)],
    ]);

    resultSection.classList.remove("hidden");
    resultSection.scrollIntoView({ behavior: "smooth", block: "start" });
});

resetButton.addEventListener("click", () => {
    form.reset();
    hideError();
    resultSection.classList.add("hidden");
    form.nama.focus();
});
