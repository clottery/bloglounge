<?php
	define('ROOT', '../../..');
	include ROOT . '/lib/includeForAdmin.php';

	requireMembership();

	if(isset($_POST['feedURL']) && !empty($_POST['feedURL'])) { 
		$userInformation = getUsers();
		if($userInformation['is_accepted'] == 'y') {
			$visibility = isset($_POST['isVisible']) ? 'y' : 'n';
			$filter = isset($_POST['filter'])?$_POST['filter']:'';

			$id = Feed::add($_POST['feedURL'], $visibility, $filter);	

			addAppMessage(_t('블로그를 추가했습니다.'));

			$targetURL = "http://{$_SERVER['HTTP_HOST']}{$service['path']}/admin/blog/list?id={$id}";
			header("Location: $targetURL");
			exit;
		}
	}

	include ROOT. '/lib/piece/adminHeader.php';

	$config = new Settings;
	$feedURL = isset($_GET['feedURL'])?$_GET['feedURL']:'';

	if($is_admin) {
?>
	<script type="text/javascript">
		function importOPML(id) {
			var obj = $(id);
			if(obj.val()=="") {
				obj.focus();
				return false;
			}
			return true;
		}

		function exportOPML() {
			return true;
		}

		function showImportFile() {
			$("#opmlImportByFile").show();
			$("#opmlImportByURL").hide();
		}

		function showImportURL() {
			$("#opmlImportByFile").hide();
			$("#opmlImportByURL").show();
		}
	</script>
<?php
	}

	if($userInformation['is_accepted'] == 'n') {
?>
	<div class="accept_wrap wrap">
			<?php echo drawGrayBoxBegin();?>	
				<div class="accept_messages">
					<?php echo _t('현재 페이지는 인증된 회원만이 사용하실 수 있습니다..');?>
				</div>
			<?php echo drawGrayBoxEnd();?>
	</div>
<?php
	} else {
?>
	<link rel="stylesheet" href="<?php echo $service['path'];?>/style/admin_blogadd.css" type="text/css" />
	<div class="wrap title_wrap">
		<h3><?php echo _t("블로그추가");?></h3>
	</div>

<?php
	if(!empty($feedURL)) {
		$feedURL = trim('http://' . str_replace('http://','',$feedURL));
		list($status, $feed, $xml) = feed::getRemoteFeed($feedURL);
?>
	<div class="wrap add_detail_wrap">
		<div class="messagebox">
			<a href="./"><?php echo _t('다른 새로운 피드를 검사하신 후 추가 하시려면 이곳을 클릭하십시오.');?></a>
		</div>

<?php 
	if(!empty($feed) && !empty($xml)) {
?>
		<form method="post">
			<input type="hidden" name="feedURL" value="<?php echo $feed['xmlURL'];?>" />
			<dl>
					<dt><?php echo _t('피드주소');?></dt>
					<dd class="text"><?php echo $feed['xmlURL']; ?></dd>
			</dl>				
			<dl>
					<dt><?php echo _t('제목');?></dt>
					<dd class="text"><?php echo $feed['title']; ?></dd>
			</dl>				
			<dl>
					<dt><?php echo _t('설명');?></dt>
					<dd class="text"><?php echo $feed['description']; ?></dd>
			</dl>		
			<dl>
					<dt><?php echo _t('주소');?></dt>
					<dd class="text"><?php echo $feed['blogURL']; ?> <!--<?php echo $feed['blogTool'];?>--></dd>
			</dl>
<?php		
	if(feed::doesExistXmlURL($feed['xmlURL'])) {
?>			
		<div class="options_wrap">
			<br />
				<?php echo _t('이 블로그는 이미 등록되어 있어 재등록 하실 수 없습니다.');?>		
			<br /><br />
			<a href="#" onclick="history.back(); return false;"><img src="<?php echo $service['path'];?>/images/admin/<?php echo Locale::get();?>/bt_back.gif" alt="<?php echo _t('뒤로');?>" /></a>

		</div>
<?php
	} else {
		$result = feed::getFeedItems($xml);
		if(count($result)>0) {
?>
			<dl>
					<dt><?php echo _t('글');?></dt>
					<dd class="text">
						<?php echo _f('가장 최신의 글 "%1"(을)를 포함한 %2개의 글이 존재합니다.','<span class="point">'.$result[0]['title'].'</span>', '<span class="cnt">'.count($result).'</span>');?>
					</dd>
			</dl>
<?php
		}
?>
			<div class="options_wrap">
					<p>
						<?php if (empty($config->filter)) { ?><input type="radio" name="useFilter" id="useFilter_no" checked="checked" />&nbsp;<label for="useFilter_no"><?php echo _t('모든 글을 수집합니다');?></label><br /><?php } ?>
						<input type="radio" name="useFilter" id="useFilter_yes" <?php if (!empty($config->filter)) { ?>checked="checked"<?php } ?>/>&nbsp;<label for="useFilter_yes"><?php echo _t('지정한 태그를 포함하는 글만 수집합니다');?></label>

						<div><?php if (empty($config->filter)) { ?><input type="text" id="feedFilter" class="input faderInput" onfocus="document.getElementsByName('useFilter')[1].checked=true;" /><div class="help"><?php echo _t('각 단어의 구분은 쉼표(,)로 합니다.');?></div><?php } else { echo $config->filter;?> <div class="help"><?php echo _t('관리자가 설정한 수집 태그 필터 설정이 우선권을 갖습니다');?></div><?php } ?></div>
					</p>
					<p>
						<input type="checkbox" name="isVisible" id="isVisible" checked="true" /> <label for="isVisible"><?php echo _t('블로그공개');?></label>
						<div class="help">
							<?php echo _t('블로그를 외부에 공개합니다. 비공개시 해당블로그의 글도 모두 비공개처리됩니다.');?>
						</div>
					</p>
			</div>

			<br />			

			<input type="image" src="<?php echo $service['path'];?>/images/admin/<?php echo Locale::get();?>/bt_add.gif" alt="<?php echo _t('추가');?>" />
		</form>
<?php
		}
	} else {
?>
		<div class="options_wrap">
			<br />
				<?php echo _t('잘못된 피드이거나 피드주소를 찾을 수 없습니다. 올바른 피드주소를 입력해주세요.');?>
			<br /><br />
			<a href="#" onclick="history.back(); return false;"><img src="<?php echo $service['path'];?>/images/admin/<?php echo Locale::get();?>/bt_back.gif" alt="<?php echo _t('뒤로');?>" /></a>

		</div>
<?php
	}
?>
	</div>
<?php		
		
	} else {
?>
	<div class="wrap add_wrap">
		<form method="get">
			<dl>
				<dt><label for="feedAddName"><?php echo _t('피드주소 ');?></label></dt>
				<dd><input id="feedAddName" name="feedURL" type="text" maxlength="100" class="input faderInput" /></dd>
			</dl>			
			<dl class="comments">
				<dt></dt>
				<dd><?php echo _t('블로그 주소를 입력하셔도 자동으로 피드주소를 검사합니다.');?></dd>
			</dl>
			<input type="image" src="<?php echo $service['path'];?>/images/admin/<?php echo Locale::get();?>/bt_feed_check.gif" alt="<?php echo _t('피드검사');?>" />
		</form>	
	</div>
<?php
	if($is_admin) {
?>
	<div  class="wrap opml_wrap">

		<h3><?php echo _t("OPML 관리");?></h3>
		<br />

		<dl>
				<dt><?php echo _t('가져오기');?></dt>
				<dd class="text">
					<a href="#" onclick="showImportURL(); return false;">웹상에서 가져오기</a> , <a href="#" onclick="showImportFile(); return false;">내 컴퓨터에서 가져오기</a>
				</dd>
		</dl>
		<dl class="comments">
			<dt></dt>
			<dd>
				<?php echo _t('OPML 파일을 읽어서 피드 목록에 추가합니다.');?>				
			</dd>
		</dl>

		<div id="opmlImportByURL" class="opmlImport">
			<form method="post" action="./opml/import/" enctype="multipart/form-data" target="_hiddenFrame" onsubmit="return importOPML('#importURL');">
				<input type="hidden" name="importType" value="url" />
				<input type="text" id="importURL" name="importURL" class="input faderInput" />

				<input type="image" src="<?php echo $service['path'];?>/images/admin/<?php echo Locale::get();?>/bt_import.gif" align="absmiddle"  alt="가져오기" />

				<div class="help">
					<?php echo _t("OPML이 위치한 URL주소를 입력하여 블로그를 추가합니다.");?>
				</div>
			</form>
		</div>

		<div id="opmlImportByFile" class="opmlImport">
			<form method="post" action="./opml/import/" enctype="multipart/form-data" target="_hiddenFrame" onsubmit="return importOPML('#importFile');">
				<input type="hidden" name="importType" value="upload" />
				<input type="file" id="importFile" name="importFile" class="input faderInput" />

				<input type="image" src="<?php echo $service['path'];?>/images/admin/<?php echo Locale::get();?>/bt_import.gif" align="absmiddle" alt="가져오기" />

				<div class="help">
					<?php echo _t("내 컴퓨터상의 OPML을 업로드하여 블로그를 추가합니다.");?>
				</div>
			</form>
		</div>

		<dl>
				<dt><?php echo _t('내보내기');?></dt>
				<dd class="text">
					<a href="./opml/export/" onclick="return exportOPML();">다운로드</a>
				</dd>
		</dl>
		<dl class="comments">
			<dt></dt>
			<dd><?php echo _t('피드 목록 전체를 OPML 파일로 저장합니다.');?></dd>
		</dl>

	</div>		
	
	<iframe id="_hiddenFrame" name="_hiddenFrame" class="hidden" src="about:blank" frameborder="0" width="0" height="0"></iframe>
<?php
	}
  }
}
	include ROOT. '/lib/piece/adminFooter.php';
?>
