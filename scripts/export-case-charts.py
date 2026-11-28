"""Append reproducible vector chart sheets to existing case PDFs; preserve original work pages.

Run with Python + reportlab + pymupdf from any directory. Replaces only sheets bearing
our generated marker, so repeated exports do not duplicate pages.
"""
from pathlib import Path
from io import BytesIO
from html import escape
import json
import pymupdf
from reportlab.pdfgen import canvas
from reportlab.platypus import Paragraph
from reportlab.lib.styles import ParagraphStyle
from reportlab.lib.colors import HexColor
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont

ROOT = Path(__file__).resolve().parents[1]
MARKER = 'Lectura visual de resultados'
COLORS = ['#147d78', '#b85530', '#6550a1', '#327bb4']

def number(value):
    return f'{value:,.0f}' if value == int(value) else f'{value:,.2f}'

def sheets(case, charts):
    stream = BytesIO()
    pdf = canvas.Canvas(stream, pagesize=(595,842))
    def paragraph(text, y, size=11, bold=False, color='#263e4c'):
        p = Paragraph(escape(str(text)), ParagraphStyle('chart',fontName='ChartBold' if bold else 'ChartBody',fontSize=size,leading=size*1.45,textColor=HexColor(color)))
        _, height = p.wrap(499,750)
        assert y-height > 65, (case['id'], text)
        p.drawOn(pdf,48,y-height)
        return y-height-12
    for chart in charts:
        y=paragraph(MARKER.upper(),794,9,True,'#147d78')
        y=paragraph(case['title'],y,21,True)
        y=paragraph(case['period'],y,10)
        y=paragraph(chart['title'],y-9,16,True)
        y=paragraph(chart['unit'],y,10)
        rows=chart['rows']
        percent='%' in chart['unit']
        if chart['type']=='donut':
            total=sum(v for _,v in rows)
            cx,cy,r=155,y-112,88
            angle=90
            for i,(_,value) in enumerate(rows):
                sweep=value/total*360
                pdf.setFillColor(HexColor(COLORS[i%4]))
                pdf.wedge(cx-r,cy-r,cx+r,cy+r,angle,-sweep,stroke=0,fill=1)
                angle-=sweep
            pdf.setFillColor(HexColor('#ffffff'));pdf.circle(cx,cy,61,stroke=0,fill=1)
            pdf.setFillColor(HexColor('#183333'));pdf.setFont('ChartBold',22);pdf.drawCentredString(cx,cy+2,number(total))
            pdf.setFont('ChartBody',10);pdf.drawCentredString(cx,cy-17,'clics totales')
            for i,(label,value) in enumerate(rows):
                yy=cy+36-i*68
                pdf.setFillColor(HexColor(COLORS[i%4]));pdf.circle(284,yy+4,5,stroke=0,fill=1)
                pdf.setFillColor(HexColor('#263e4c'));pdf.setFont('ChartBold',12);pdf.drawString(299,yy,label)
                pdf.setFont('ChartBody',11);pdf.drawString(299,yy-21,f'{number(value)} clics / {value/total*100:.1f}%')
            y=cy-r-30
        elif chart['type']=='stat':
            for value,label,_ in case['metrics']:
                y=paragraph(value,y-8,32,True)
                y=paragraph(label,y,11)
        else:
            if chart['type']=='rate':
                y=paragraph(number(rows[0][1])+'%',y-8,40,True)
            maximum=chart['max']
            for i,(label,value) in enumerate(rows):
                pdf.setFillColor(HexColor('#263e4c'));pdf.setFont('ChartBody',11);pdf.drawString(48,y-14,label)
                pdf.setFont('ChartBold',11);pdf.drawRightString(547,y-14,number(value)+('%' if percent else ''))
                pdf.setFillColor(HexColor('#e4ebeb'));pdf.rect(48,y-38,499,14,fill=1,stroke=0)
                pdf.setFillColor(HexColor(COLORS[i%4]));pdf.rect(48,y-38,499*value/maximum,14,fill=1,stroke=0)
                if 'baseline' in chart:
                    x=48+499*chart['baseline']/maximum
                    pdf.setStrokeColor(HexColor('#263e4c'));pdf.line(x,y-40,x,y-22)
                y-=64
            pdf.setFillColor(HexColor('#536775'));pdf.setFont('ChartBody',9)
            pdf.drawString(48,y+9,'0');pdf.drawRightString(547,y+9,number(maximum)+('%' if percent else ''))
            y-=18
        y=paragraph(chart['takeaway'],y-12,13,True)
        y=paragraph(chart['note'],y,10)
        paragraph('Referencia: '+case.get('source_label',case['source']),y-8,9)
        pdf.setFont('ChartBody',8);pdf.setFillColor(HexColor('#536775'))
        pdf.drawString(48,32,'Jovani Treviño · Marketing Intelligence Digital')
        pdf.drawRightString(547,32,MARKER)
        pdf.showPage()
    pdf.save()
    return stream.getvalue()

def main():
    fonts=Path('C:/Windows/Fonts')
    pdfmetrics.registerFont(TTFont('ChartBody',str(fonts/'arial.ttf')))
    pdfmetrics.registerFont(TTFont('ChartBold',str(fonts/'arialbd.ttf')))
    cases=json.loads((ROOT/'blog/content/professional-cases.json').read_text(encoding='utf-8-sig'))
    configs=json.loads((ROOT/'blog/content/professional-charts.json').read_text(encoding='utf-8-sig'))
    count=0
    for case in cases:
        if case['id'] not in configs: continue
        config=configs[case['id']]
        primary={**case['chart'],**config}
        charts=[primary]+([config['extra']] if 'extra' in config else [])
        appendix=pymupdf.open(stream=sheets(case,charts),filetype='pdf')
        for suffix in ['', '-visual']:
            target=ROOT/'Jovani/assets/downloads/casos'/f'{case["id"]}{suffix}.pdf'
            if not target.exists(): continue
            document=pymupdf.open(target)
            while len(document) and MARKER in document[-1].get_text(): document.delete_page(-1)
            document.insert_pdf(appendix)
            temporary=target.with_suffix('.charts.tmp.pdf')
            document.save(temporary,garbage=4,deflate=True);document.close()
            temporary.replace(target)
            count+=1
        appendix.close()
    print(f'Updated {count} PDFs from {len(configs)} cases; vector chart sheets regenerated.')

if __name__=='__main__': main()
