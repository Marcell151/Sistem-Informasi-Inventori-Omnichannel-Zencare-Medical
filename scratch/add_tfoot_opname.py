with open('inventori/stok_opname.php', 'r', encoding='utf-8', errors='ignore') as f:
    text = f.read()

# Locate table closing in Verifikasi Admin
target = """                                    <?php endforeach; ?>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>"""

tfoot_html = """                                    <?php endforeach; ?>
                                <?php endforeach; endif; ?>
                            </tbody>
                            <!-- === ESTIMASI NILAI KERUGIAN OPNAME (BERBASIS HARGA BELI) === -->
                            <tfoot class="bg-slate-100 border-t-2 border-slate-300 font-bold text-slate-800">
                                <tr>
                                    <td colspan="8" class="py-3 px-4 text-right uppercase tracking-wider text-[11px] font-sans">
                                        Total Keseluruhan (Grand Total) Estimasi Nilai Kerugian Persediaan:
                                    </td>
                                    <td class="py-3 px-3 text-center text-rose-700 font-mono text-xs whitespace-nowrap bg-rose-50/80 border-x border-rose-200">
                                        <span class="block font-extrabold">Rp <?= number_format($totalEstLoss, 0, ',', '.') ?></span>
                                        <span class="text-[9px] text-rose-600 font-sans font-medium block">Acuan Harga Beli</span>
                                    </td>
                                    <td colspan="2" class="py-3 px-3 text-center text-[10px] text-slate-600 font-sans">
                                        <?= ($stats['berselisih'] - $stats['sudah_dimutasi']) > 0 ? ($stats['berselisih'] - $stats['sudah_dimutasi']) . ' Item Perlu Rekonsiliasi' : 'Semua Selisih Tuntas Direkonsiliasi' ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>"""

assert target in text, "Target tbody closure not found in stok_opname.php"
text = text.replace(target, tfoot_html, 1)

with open('inventori/stok_opname.php', 'w', encoding='utf-8') as f:
    f.write(text)

print("Updated stok_opname.php with tfoot Grand Total successfully!")

