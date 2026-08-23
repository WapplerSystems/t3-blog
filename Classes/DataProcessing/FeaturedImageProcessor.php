<?php

namespace WapplerSystems\Blog\DataProcessing;


use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use TYPO3\CMS\Frontend\Resource\FileCollector;

/**
 *
 */
class FeaturedImageProcessor implements DataProcessorInterface
{
    /**
     * Process data of a record to resolve File objects to the view
     *
     * @param ContentObjectRenderer $cObj The data of the content element or page
     * @param array $contentObjectConfiguration The configuration of Content Object
     * @param array $processorConfiguration The configuration of this processor
     * @param array $processedData Key/value store of processed data (e.g. to be passed to a Fluid View)
     * @return array the processed data as key/value store
     */
    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData)
    {
        if (isset($processorConfiguration['if.']) && !$cObj->checkIf($processorConfiguration['if.'])) {
            return $processedData;
        }

        $page = $this->getActiveRecord($cObj);

        $processedData['hero']['featuredImage'] = $this->getSlideRecords($cObj, $page['uid'], 'featured_image', 0);

        return $processedData;
    }


    /**
     * Get records, optionally sliding up the page rootline
     *
     * @param ContentObjectRenderer $cObj
     * @param int $pageUid
     * @param $fieldName
     * @param int $limit
     * @return FileReference[]|null
     */
    protected function getSlideRecords(ContentObjectRenderer $cObj, $pageUid, $fieldName, int $limit = 0)
    {

        if ($limit <= 0) {
            $images = $this->getFileReference($cObj, $fieldName);
            if (count($images) > 0) {
                return $images;
            }
        }
        $rootLine = GeneralUtility::makeInstance(RootlineUtility::class,$pageUid)->get();
        if ($limit >= 0) {
            $rootLine = array_slice($rootLine, 0, $limit + 1);
        }

        foreach ($rootLine as $page) {
            $images = $this->getFileReference($cObj, $fieldName, $page);
            if (count($images) > 0) {
                return $images;
            }
        }
        return null;
    }


    /**
     * @param ContentObjectRenderer $cObj
     * @param $fieldName
     * @param array|null $page
     * @return array
     */
    private function getFileReference(ContentObjectRenderer $cObj, $fieldName, ?array $page = null)
    {

        /** @var FileCollector $fileCollector */
        $fileCollector = GeneralUtility::makeInstance(FileCollector::class);
        $fileCollector->addFilesFromRelation('pages', $fieldName, $page ?? $cObj->data);

        return $fileCollector->getFiles();
    }

    /**
     * AbstractRecordResource usually uses the current cObj as reference,
     * but the page is needed here
     *
     * @return array
     */
    public function getActiveRecord(ContentObjectRenderer $cObj)
    {
        $pageInformation = $cObj->getRequest()->getAttribute('frontend.page.information');
        if ($pageInformation !== null) {
            return $pageInformation->getPageRecord();
        }
        return [];
    }

}
