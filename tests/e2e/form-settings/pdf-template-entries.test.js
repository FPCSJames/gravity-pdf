import Pdf from '../page-model/helpers/pdf'
import PdfTemplateEntries from '../page-model/form-settings/pdf-template-entries'
import { link } from '../page-model/helpers/field'

const pdf = new Pdf()
const run = new PdfTemplateEntries()

fixture`PDF Template - Entries Test`

test('should successfully add new PDF template into form entries', async t => {
  // Actions
  await pdf.navigateAddPdf('gf_edit_forms&view=settings&subview=pdf&id=1')
  await t.click(link('#gform_tabs', 'PDF'))

  // Assertions
  await t.expect(pdf.template.count).eql(1)
})

test('should successfully switch from Active template to Inactive', async t => {
  // Actions
  await pdf.navigatePdfSection('gf_edit_forms&view=settings&subview=pdf&id=1')
  await t
    .click(run.toggleSwitch)

  // Assertions
  await t.expect(run.inActiveTemplate.exists).ok()
})

test('should double check if the option View PDF link is disabled when template is Inactive', async t => {
  // Actions
  await pdf.navigatePdfSection('gf_entries&id=1')
  await t.hover(run.entryItem)

  // Assertions
  await t.expect(run.viewPdfLink.exists).notOk()
})

test('should successfully switch from Inactive template to Active', async t => {
  // Actions
  await pdf.navigatePdfSection('gf_edit_forms&view=settings&subview=pdf&id=1')
  await t.click(run.toggleSwitch)

  // Assertions
  await t.expect(run.activeTemplate.exists).ok()
})

test('should double check if the option View PDF link is enabled when template is Active', async t => {
  // Actions
  await pdf.navigatePdfSection('gf_entries&id=1')
  await t.hover(run.entryItem)

  // Assertions
  await t.expect(run.viewPdfLink.exists).ok()
})

test('should double check if the option View PDF link isn\'t shown when the PDF is Active but the PDF Conditional Logic fails.', async t => {
  // Actions
  await pdf.navigatePdfSection('gf_edit_forms&view=settings&subview=pdf&id=1')
  await t
    .hover(run.templateList)
    .click(run.editLink)
    .click(run.enableConditionalLogic)
    .click(run.updatePdfButton)
  await pdf.navigatePdfSection('gf_entries&id=1')
  await t.hover(run.entryItem)

  // Assertions
  await t.expect(run.viewPdfLink.exists).notOk()
})

test('should successfully edit and update existing template using the Edit link option', async t => {
  // Actions
  await pdf.navigatePdfSection('gf_edit_forms&view=settings&subview=pdf&id=1')
  await t
    .hover(run.templateList)
    .click(run.editLink)
    .typeText(run.name, 'Test PDF Template Updated', { replace: true })
    .typeText(run.fileName, 'testpdftemplateupdated', { replace: true })
    .click(run.updatePdfButton)
    .click(run.backToTemplateListLink)

  // Assertions
  await t.expect(run.templateDetail.innerText).contains('Test PDF Template Updated')
})

test('should successfully duplicate existing PDF template using the Duplicate link option', async t => {
  // Actions
  await pdf.navigatePdfSection('gf_edit_forms&view=settings&subview=pdf&id=1')
  await t
    .hover(run.options)
    .click(run.duplicateLink)

  // Assertions
  await t.expect(run.templateList.find('tr').count).eql(2)
})

test('reset/clean PDF templates from the list for the next test', async t => {
  // Actions
  await pdf.navigateDeletePdfEntries('gf_edit_forms&view=settings&subview=pdf&id=1')

  // Assertions
  await t.expect(pdf.template.count).eql(0)
})
